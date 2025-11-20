<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditLogService;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Bill::with('vendor');
        
        $allowedSorts = ['bill_number', 'bill_date', 'due_date', 'vendor_id', 'total', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $bills = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        return view('bills.index', compact('bills', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Bill::with('vendor');
        
        $allowedSorts = ['bill_number', 'bill_date', 'due_date', 'vendor_id', 'total', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $bills = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'bills-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($bills) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Bill Number', 'Vendor', 'Bill Date', 'Due Date', 'Subtotal', 'VAT Amount', 'Total', 'Status', 'Updated At', 'Created At']);
            
            foreach ($bills as $bill) {
                fputcsv($file, [
                    $bill->bill_number,
                    $bill->vendor->name ?? 'N/A',
                    $bill->bill_date->format('Y-m-d'),
                    $bill->due_date->format('Y-m-d'),
                    number_format($bill->subtotal, 2),
                    number_format($bill->vat_amount, 2),
                    number_format($bill->total, 2),
                    ucfirst($bill->status),
                    $bill->updated_at->format('Y-m-d H:i:s'),
                    $bill->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = \App\Models\Vendor::where('is_active', true)->get();
        return view('bills.create', compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'nullable|numeric|min:0',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        // Calculate totals
        $subtotal = 0;
        $vatAmount = 0;

        foreach ($request->lines as $line) {
            $qty = $line['quantity'] ?? 1;
            $price = $line['unit_price'] ?? 0;
            $taxRate = $line['tax_rate'] ?? 15;
            $lineSubtotal = $qty * $price;
            $subtotal += $lineSubtotal;
            $vatAmount += $lineSubtotal * ($taxRate / 100);
        }

        $total = $subtotal + $vatAmount;

        // Get default expense account (first Expense account for the tenant)
        $defaultExpenseAccount = \App\Models\Account::where('tenant_id', auth()->user()->tenant_id)
            ->where('type', 'Expense')
            ->where('is_active', true)
            ->first();

        if (!$defaultExpenseAccount) {
            return back()->withErrors(['account' => 'No Expense account found. Please create an Expense account first.'])
                ->withInput();
        }

        $bill = \App\Models\Bill::create([
            'tenant_id' => auth()->user()->tenant_id,
            'bill_number' => $this->generateBillNumber(),
            'vendor_id' => $validated['vendor_id'],
            'bill_date' => $validated['bill_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'status' => 'received',
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'attachments' => $validated['attachments'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->lines as $line) {
            $qty = $line['quantity'] ?? 1;
            $price = $line['unit_price'] ?? 0;
            $vatPercent = $line['tax_rate'] ?? 15;
            $lineSubtotal = $qty * $price;
            $lineVat = $lineSubtotal * ($vatPercent / 100);
            $lineTotal = $lineSubtotal + $lineVat;

            $bill->lines()->create([
                'description' => $line['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'vat_percent' => $vatPercent,
                'vat_amount' => $lineVat,
                'line_total' => $lineTotal,
                'discount_percent' => 0,
                'account_id' => $defaultExpenseAccount->id,
            ]);
        }

        AuditLogService::log(
            'bills',
            'create',
            "Created bill {$bill->bill_number}",
            ['bill_id' => $bill->id]
        );

        return redirect()->route('bills.index')
            ->with('success', 'Bill created successfully.');
    }

    protected function generateBillNumber(): string
    {
        $datePrefix = date('Ymd');
        $count = \App\Models\Bill::whereDate('bill_date', today())->count() + 1;
        return 'BILL-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bill = \App\Models\Bill::with(['vendor', 'lines.account', 'createdBy'])->findOrFail($id);
        
        return view('bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bill = \App\Models\Bill::findOrFail($id);
        
        // Check if bill has payments
        if ($bill->payments()->count() > 0) {
            return redirect()->route('bills.index')
                ->with('error', 'Cannot delete bill with existing payments. Please delete payments first.');
        }
        
        // Only allow deletion of draft/received bills
        if (!in_array($bill->status, ['received', 'draft'])) {
            return redirect()->route('bills.index')
                ->with('error', 'Cannot delete bill that has been paid or processed.');
        }
        
        $bill->delete();

        AuditLogService::log(
            'bills',
            'delete',
            "Deleted bill {$bill->bill_number}",
            ['bill_id' => $bill->id]
        );
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully.');
    }

    public function markReceived($id)
    {
        $bill = \App\Models\Bill::findOrFail($id);
        
        if ($bill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be marked as received.');
        }
        
        $bill->update(['status' => 'received']);

        AuditLogService::log(
            'bills',
            'status_change',
            "Bill {$bill->bill_number} marked as received",
            ['bill_id' => $bill->id]
        );
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill marked as received successfully.');
    }

    public function markPaid($id)
    {
        $bill = \App\Models\Bill::findOrFail($id);
        
        if ($bill->status === 'paid') {
            return back()->with('error', 'Bill is already marked as paid.');
        }
        
        if ($bill->status === 'cancelled') {
            return back()->with('error', 'Cannot mark cancelled bill as paid.');
        }
        
        $bill->update(['status' => 'paid']);

        AuditLogService::log(
            'bills',
            'status_change',
            "Bill {$bill->bill_number} marked as paid",
            ['bill_id' => $bill->id]
        );
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill marked as paid successfully.');
    }

    public function cancel($id)
    {
        $bill = \App\Models\Bill::findOrFail($id);
        
        if ($bill->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid bill.');
        }
        
        if ($bill->status === 'cancelled') {
            return back()->with('error', 'Bill is already cancelled.');
        }
        
        $bill->update(['status' => 'cancelled']);

        AuditLogService::log(
            'bills',
            'status_change',
            "Bill {$bill->bill_number} cancelled",
            ['bill_id' => $bill->id]
        );
        
        return redirect()->route('bills.index')
            ->with('success', 'Bill cancelled successfully.');
    }
}
