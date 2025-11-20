<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditLogService;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Invoice::with('customer');
        
        $allowedSorts = ['invoice_number', 'invoice_date', 'due_date', 'customer_id', 'total', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $invoices = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        return view('invoices.index', compact('invoices', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Invoice::with('customer');
        
        $allowedSorts = ['invoice_number', 'invoice_date', 'due_date', 'customer_id', 'total', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $invoices = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'invoices-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Invoice Number', 'Customer', 'Invoice Date', 'Due Date', 'Subtotal', 'VAT Amount', 'Total', 'Status', 'Updated At', 'Created At']);
            
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->customer->name ?? 'N/A',
                    $invoice->invoice_date->format('Y-m-d'),
                    $invoice->due_date->format('Y-m-d'),
                    number_format($invoice->subtotal, 2),
                    number_format($invoice->vat_amount, 2),
                    number_format($invoice->total, 2),
                    ucfirst($invoice->status),
                    $invoice->updated_at->format('Y-m-d H:i:s'),
                    $invoice->created_at->format('Y-m-d H:i:s'),
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
        $customers = \App\Models\Customer::where('is_active', true)->get();
        return view('invoices.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
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

        // Get default revenue account (first Revenue account for the tenant)
        $defaultRevenueAccount = \App\Models\Account::where('tenant_id', auth()->user()->tenant_id)
            ->where('type', 'Revenue')
            ->where('is_active', true)
            ->first();

        if (!$defaultRevenueAccount) {
            return back()->withErrors(['account' => 'No Revenue account found. Please create a Revenue account first.'])
                ->withInput();
        }

        $invoice = \App\Models\Invoice::create([
            'tenant_id' => auth()->user()->tenant_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_id' => $validated['customer_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'status' => 'draft',
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

            $invoice->lines()->create([
                'description' => $line['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'vat_percent' => $vatPercent,
                'vat_amount' => $lineVat,
                'line_total' => $lineTotal,
                'discount_percent' => 0,
                'account_id' => $defaultRevenueAccount->id,
            ]);
        }

        AuditLogService::log(
            'invoices',
            'create',
            "Created invoice {$invoice->invoice_number}",
            ['invoice_id' => $invoice->id]
        );

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    protected function generateInvoiceNumber(): string
    {
        $datePrefix = date('Ymd');
        $count = \App\Models\Invoice::whereDate('invoice_date', today())->count() + 1;
        return 'INV-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = \App\Models\Invoice::with(['customer', 'lines.account', 'createdBy'])->findOrFail($id);
        
        return view('invoices.show', compact('invoice'));
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
        $invoice = \App\Models\Invoice::findOrFail($id);
        
        // Check if invoice has payments
        if ($invoice->payments()->count() > 0) {
            return redirect()->route('invoices.index')
                ->with('error', 'Cannot delete invoice with existing payments. Please delete payments first.');
        }
        
        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Cannot delete invoice that is not in draft status.');
        }
        
        $invoice->delete();

        AuditLogService::log(
            'invoices',
            'delete',
            "Deleted invoice {$invoice->invoice_number}",
            ['invoice_id' => $invoice->id]
        );
        
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function send($id)
    {
        $invoice = \App\Models\Invoice::findOrFail($id);
        
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be sent.');
        }
        
        $invoice->update(['status' => 'sent']);

        AuditLogService::log(
            'invoices',
            'status_change',
            "Invoice {$invoice->invoice_number} sent",
            ['invoice_id' => $invoice->id]
        );
        
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice sent successfully.');
    }

    public function markPaid($id)
    {
        $invoice = \App\Models\Invoice::findOrFail($id);
        
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice is already marked as paid.');
        }
        
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot mark cancelled invoice as paid.');
        }
        
        $invoice->update(['status' => 'paid']);

        AuditLogService::log(
            'invoices',
            'status_change',
            "Invoice {$invoice->invoice_number} marked as paid",
            ['invoice_id' => $invoice->id]
        );
        
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice marked as paid successfully.');
    }

    public function cancel($id)
    {
        $invoice = \App\Models\Invoice::findOrFail($id);
        
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid invoice.');
        }
        
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Invoice is already cancelled.');
        }
        
        $invoice->update(['status' => 'cancelled']);

        AuditLogService::log(
            'invoices',
            'status_change',
            "Invoice {$invoice->invoice_number} cancelled",
            ['invoice_id' => $invoice->id]
        );
        
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice cancelled successfully.');
    }
}
