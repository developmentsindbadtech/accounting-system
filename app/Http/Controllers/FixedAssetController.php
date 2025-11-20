<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditLogService;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\FixedAsset::with('category');
        
        $allowedSorts = ['asset_number', 'name', 'purchase_date', 'purchase_cost', 'net_book_value', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $assets = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        return view('fixed-assets.index', compact('assets', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\FixedAsset::with('category');
        
        $allowedSorts = ['asset_number', 'name', 'purchase_date', 'purchase_cost', 'net_book_value', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $assets = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'fixed-assets-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($assets) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Asset Number', 'Name', 'Category', 'Purchase Date', 'Purchase Cost', 'Useful Life (Years)', 'Depreciation Method', 'Accumulated Depreciation', 'Net Book Value', 'Status', 'Updated At', 'Created At']);
            
            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->asset_number,
                    $asset->name,
                    $asset->category->name ?? '',
                    $asset->purchase_date->format('Y-m-d'),
                    number_format($asset->purchase_cost, 2),
                    $asset->useful_life_years,
                    ucfirst(str_replace('-', ' ', $asset->depreciation_method)),
                    number_format($asset->accumulated_depreciation, 2),
                    number_format($asset->net_book_value, 2),
                    ucfirst($asset->status),
                    $asset->updated_at->format('Y-m-d H:i:s'),
                    $asset->created_at->format('Y-m-d H:i:s'),
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
        $tenantId = auth()->user()->tenant_id;
        
        $categories = \App\Models\AssetCategory::where('tenant_id', $tenantId)->get();
        $accounts = \App\Models\Account::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('type', 'Asset')
            ->orderBy('code')
            ->get();
        $expenseAccounts = \App\Models\Account::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('type', 'Expense')
            ->orderBy('code')
            ->get();
        
        return view('fixed-assets.create', compact('categories', 'accounts', 'expenseAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $validated = $request->validate([
            'asset_number' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $exists = \App\Models\FixedAsset::where('tenant_id', $tenantId)
                        ->where('asset_number', $value)
                        ->exists();
                    if ($exists) {
                        $fail('The asset number has already been taken.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'category_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if ($value) {
                        $exists = \App\Models\AssetCategory::where('tenant_id', $tenantId)
                            ->where('id', $value)
                            ->exists();
                        if (!$exists) {
                            $fail('The selected category is invalid.');
                        }
                    }
                },
            ],
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'depreciation_method' => 'required|in:straight-line,reducing-balance',
            'useful_life_years' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'asset_account_id' => 'required|exists:accounts,id',
            'depreciation_expense_account_id' => 'required|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'required|exists:accounts,id',
            'attachments' => 'nullable|string',
        ]);

        // Verify accounts belong to tenant
        $accountIds = [
            $validated['asset_account_id'],
            $validated['depreciation_expense_account_id'],
            $validated['accumulated_depreciation_account_id'],
        ];
        
        $validAccounts = \App\Models\Account::where('tenant_id', $tenantId)
            ->whereIn('id', $accountIds)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        
        if (count($validAccounts) !== count($accountIds)) {
            return back()->withErrors(['accounts' => 'One or more selected accounts are invalid or inactive.'])->withInput();
        }

        $validated['tenant_id'] = $tenantId;
        $validated['accumulated_depreciation'] = 0;
        $validated['net_book_value'] = $validated['purchase_cost'];
        $validated['salvage_value'] = $validated['salvage_value'] ?? 0;
        $validated['status'] = 'active';

        try {
            $asset = \App\Models\FixedAsset::create($validated);

            AuditLogService::log(
                'fixed_assets',
                'create',
                "Created fixed asset {$asset->asset_number} - {$asset->name}",
                ['asset_id' => $asset->id]
            );

            return redirect()->route('fixed-assets.index')
                ->with('success', 'Fixed asset created successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Fixed Asset Creation Error', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'An error occurred while creating the fixed asset.';
            
            if (str_contains($e->getMessage(), 'UNIQUE constraint')) {
                $errorMessage = 'The asset number already exists. Please use a different asset number.';
            } elseif (str_contains($e->getMessage(), 'NOT NULL constraint')) {
                $errorMessage = 'Required fields are missing. Please check your input.';
            } elseif (str_contains($e->getMessage(), 'FOREIGN KEY constraint')) {
                $errorMessage = 'Invalid account selected. Please check your account selections.';
            }
            
            return back()->withErrors(['database' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            \Log::error('Fixed Asset Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['database' => 'An unexpected error occurred. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $asset = \App\Models\FixedAsset::with([
            'category',
            'assetAccount',
            'depreciationExpenseAccount',
            'accumulatedDepreciationAccount',
            'depreciationEntries' => function($query) {
                $query->orderBy('entry_date', 'desc')->limit(10);
            }
        ])->findOrFail($id);
        
        return view('fixed-assets.show', compact('asset'));
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
        $asset = \App\Models\FixedAsset::findOrFail($id);
        
        if ($asset->status === 'disposed') {
            return redirect()->route('fixed-assets.index')
                ->with('error', 'Cannot delete a disposed asset.');
        }
        
        // Check if asset has depreciation entries
        if ($asset->depreciationEntries()->count() > 0) {
            return redirect()->route('fixed-assets.index')
                ->with('error', 'Cannot delete asset with depreciation history. Please dispose it instead.');
        }
        
        $asset->delete();

        AuditLogService::log(
            'fixed_assets',
            'delete',
            "Deleted fixed asset {$asset->asset_number} - {$asset->name}",
            ['asset_id' => $asset->id]
        );
        
        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed asset deleted successfully.');
    }

    public function dispose($id, Request $request)
    {
        $validated = $request->validate([
            'disposal_date' => 'required|date',
        ]);
        
        $asset = \App\Models\FixedAsset::findOrFail($id);
        
        if ($asset->status === 'disposed') {
            return back()->with('error', 'Asset is already disposed.');
        }
        
        $asset->update([
            'status' => 'disposed',
            'disposal_date' => $validated['disposal_date'],
        ]);

        AuditLogService::log(
            'fixed_assets',
            'status_change',
            "Disposed fixed asset {$asset->asset_number} - {$asset->name} on {$validated['disposal_date']}",
            ['asset_id' => $asset->id]
        );
        
        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed asset disposed successfully.');
    }
}
