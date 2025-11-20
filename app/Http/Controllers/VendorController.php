<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditLogService;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Vendor::query();
        
        $allowedSorts = ['code', 'name', 'email', 'phone', 'balance', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $vendors = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        return view('vendors.index', compact('vendors', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Vendor::query();
        
        $allowedSorts = ['code', 'name', 'email', 'phone', 'balance', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $vendors = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'vendors-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($vendors) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Code', 'Name', 'Email', 'Phone', 'Address', 'Tax ID', 'Payment Terms', 'Balance', 'Status', 'Updated At', 'Created At']);
            
            foreach ($vendors as $vendor) {
                fputcsv($file, [
                    $vendor->code ?? '',
                    $vendor->name,
                    $vendor->email ?? '',
                    $vendor->phone ?? '',
                    $vendor->address ?? '',
                    $vendor->tax_id ?? '',
                    $vendor->payment_terms ?? '',
                    number_format($vendor->balance, 2),
                    $vendor->is_active ? 'Active' : 'Inactive',
                    $vendor->updated_at->format('Y-m-d H:i:s'),
                    $vendor->created_at->format('Y-m-d H:i:s'),
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
        return view('vendors.create');
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
            'payment_terms' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'attachments' => 'nullable|string',
        ]);

        // Validate code uniqueness only if provided
        if (!empty($validated['code'])) {
            $exists = \App\Models\Vendor::where('tenant_id', auth()->user()->tenant_id)
                ->where('code', $validated['code'])
                ->exists();
            
            if ($exists) {
                return back()->withErrors(['code' => 'The vendor code has already been taken.'])
                    ->withInput();
            }
        }

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['balance'] = 0;
        $validated['is_active'] = $request->has('is_active');

        $vendor = \App\Models\Vendor::create($validated);

        AuditLogService::log(
            'vendors',
            'create',
            "Created vendor {$vendor->name}",
            ['vendor_id' => $vendor->id]
        );

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vendor = \App\Models\Vendor::with(['bills' => function($query) {
            $query->orderBy('bill_date', 'desc')->limit(10);
        }])->findOrFail($id);
        
        return view('vendors.show', compact('vendor'));
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
        $vendor = \App\Models\Vendor::findOrFail($id);
        
        // Check if vendor has bills
        if ($vendor->bills()->count() > 0) {
            return redirect()->route('vendors.index')
                ->with('error', 'Cannot delete vendor with existing bills. Please delete or reassign bills first.');
        }
        
        // Check if vendor has bill payments
        if (\App\Models\BillPayment::where('vendor_id', $vendor->id)->count() > 0) {
            return redirect()->route('vendors.index')
                ->with('error', 'Cannot delete vendor with existing payments. Please delete or reassign payments first.');
        }
        
        $vendor->delete();

        AuditLogService::log(
            'vendors',
            'delete',
            "Deleted vendor {$vendor->name}",
            ['vendor_id' => $vendor->id]
        );
        
        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    public function toggleActive($id)
    {
        $vendor = \App\Models\Vendor::findOrFail($id);
        $vendor->update(['is_active' => !$vendor->is_active]);
        
        $status = $vendor->is_active ? 'activated' : 'deactivated';

        AuditLogService::log(
            'vendors',
            'status_change',
            "Vendor {$vendor->name} {$status}",
            ['vendor_id' => $vendor->id]
        );

        return redirect()->route('vendors.index')
            ->with('success', "Vendor {$status} successfully.");
    }
}
