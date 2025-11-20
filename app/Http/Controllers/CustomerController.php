<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditLogService;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Customer::query();
        
        $allowedSorts = ['code', 'name', 'email', 'phone', 'balance', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $customers = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        return view('customers.index', compact('customers', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Customer::query();
        
        $allowedSorts = ['code', 'name', 'email', 'phone', 'balance', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $customers = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'customers-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Code', 'Name', 'Email', 'Phone', 'Address', 'Tax ID', 'Credit Limit', 'Balance', 'Status', 'Updated At', 'Created At']);
            
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->code ?? '',
                    $customer->name,
                    $customer->email ?? '',
                    $customer->phone ?? '',
                    $customer->address ?? '',
                    $customer->tax_id ?? '',
                    number_format($customer->credit_limit ?? 0, 2),
                    number_format($customer->balance, 2),
                    $customer->is_active ? 'Active' : 'Inactive',
                    $customer->updated_at->format('Y-m-d H:i:s'),
                    $customer->created_at->format('Y-m-d H:i:s'),
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
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'attachments' => 'nullable|string',
        ]);

        // Validate code uniqueness only if provided
        if (!empty($validated['code'])) {
            $exists = \App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)
                ->where('code', $validated['code'])
                ->exists();
            
            if ($exists) {
                return back()->withErrors(['code' => 'The customer code has already been taken.'])
                    ->withInput();
            }
        }

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['balance'] = 0;
        $validated['is_active'] = $request->has('is_active');

        $customer = \App\Models\Customer::create($validated);

        AuditLogService::log(
            'customers',
            'create',
            "Created customer {$customer->name}",
            ['customer_id' => $customer->id]
        );

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = \App\Models\Customer::with(['invoices' => function($query) {
            $query->orderBy('invoice_date', 'desc')->limit(10);
        }])->findOrFail($id);
        
        return view('customers.show', compact('customer'));
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
        $customer = \App\Models\Customer::findOrFail($id);
        
        // Check if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing invoices. Please delete or reassign invoices first.');
        }
        
        // Check if customer has payments
        if ($customer->payments()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing payments. Please delete or reassign payments first.');
        }
        
        $customer->delete();

        AuditLogService::log(
            'customers',
            'delete',
            "Deleted customer {$customer->name}",
            ['customer_id' => $customer->id]
        );
        
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function toggleActive($id)
    {
        $customer = \App\Models\Customer::findOrFail($id);
        $customer->update(['is_active' => !$customer->is_active]);
        
        $status = $customer->is_active ? 'activated' : 'deactivated';

        AuditLogService::log(
            'customers',
            'status_change',
            "Customer {$customer->name} {$status}",
            ['customer_id' => $customer->id]
        );

        return redirect()->route('customers.index')
            ->with('success', "Customer {$status} successfully.");
    }
}
