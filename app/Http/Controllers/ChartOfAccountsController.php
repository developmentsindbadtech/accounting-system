<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = Account::query();
        
        // Validate sort column
        $allowedSorts = ['code', 'name', 'type', 'level', 'balance', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        // Validate sort direction
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $accounts = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        
        return view('chart-of-accounts.index', compact('accounts', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = Account::query();
        
        $allowedSorts = ['code', 'name', 'type', 'level', 'balance', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $accounts = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'chart-of-accounts-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($accounts) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, ['Code', 'Name', 'Type', 'Sub Type', 'Level', 'Balance', 'Status', 'Updated At', 'Created At']);
            
            // Data
            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->code,
                    $account->name,
                    $account->type,
                    $account->sub_type ?? '',
                    $account->level,
                    number_format($account->balance, 2),
                    $account->is_active ? 'Active' : 'Inactive',
                    $account->updated_at->format('Y-m-d H:i:s'),
                    $account->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $parentAccounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();
        
        return view('chart-of-accounts.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('accounts', 'code')->where('tenant_id', auth()->user()->tenant_id)
            ],
            'name' => 'required|string',
            'type' => 'required|in:Asset,Liability,Equity,Revenue,Expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
            'attachments' => 'nullable|string',
        ]);
        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['level'] = $request->parent_id 
            ? Account::find($request->parent_id)->level + 1 
            : 1;
        if ($request->has('attachments')) {
            $validated['attachments'] = $request->attachments;
        }

        $account = Account::create($validated);

        AuditLogService::log(
            'chart_of_accounts',
            'create',
            "Created account {$account->code} - {$account->name}"
        );

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show($id)
    {
        // Find account - the global tenant scope will automatically filter by tenant_id
        try {
            $account = Account::with(['parent', 'children'])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Account not found or access denied.');
        }

        return view('chart-of-accounts.show', compact('account'));
    }

    public function edit($id)
    {
        // Find account - the global tenant scope will automatically filter by tenant_id
        try {
            $account = Account::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Account not found or access denied.');
        }

        $parentAccounts = Account::where('id', '!=', $account->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.edit', compact('account', 'parentAccounts'));
    }

    public function update(Request $request, $id)
    {
        // Find account - the global tenant scope will automatically filter by tenant_id
        try {
            $account = Account::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Account not found or access denied.');
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('accounts', 'code')->where('tenant_id', auth()->user()->tenant_id)->ignore($account->id)
            ],
            'name' => 'required|string',
            'type' => 'required|in:Asset,Liability,Equity,Revenue,Expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
            'attachments' => 'nullable|string',
        ]);

        $validated['level'] = $request->parent_id 
            ? Account::find($request->parent_id)->level + 1 
            : 1;
        if ($request->has('attachments')) {
            $validated['attachments'] = $request->attachments;
        }

        $account->update($validated);

        AuditLogService::log(
            'chart_of_accounts',
            'update',
            "Updated account {$account->code} - {$account->name}"
        );

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy($id)
    {
        // Find account - the global tenant scope will automatically filter by tenant_id
        try {
            $account = Account::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Account not found or access denied.');
        }

        // Check if account has child accounts
        $childCount = Account::where('parent_id', $account->id)->count();
        if ($childCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account with child accounts. Please delete or reassign child accounts first.');
        }

        // Check if account is used in journal entries
        $journalEntryCount = \App\Models\JournalEntryLine::where('account_id', $account->id)->count();
        if ($journalEntryCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account that is used in journal entries. Please delete or modify the journal entries first.');
        }

        // Check if account is used in general ledger entries
        $ledgerCount = \App\Models\GeneralLedgerEntry::where('account_id', $account->id)->count();
        if ($ledgerCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account that has general ledger entries. This account has transaction history.');
        }

        // Check if account is used in invoice lines
        $invoiceLineCount = \App\Models\InvoiceLine::where('account_id', $account->id)->count();
        if ($invoiceLineCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account that is used in invoices. Please delete or modify the invoices first.');
        }

        // Check if account is used in bill lines
        $billLineCount = \App\Models\BillLine::where('account_id', $account->id)->count();
        if ($billLineCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account that is used in bills. Please delete or modify the bills first.');
        }

        // Check if account is used in fixed assets
        $fixedAssetCount = \App\Models\FixedAsset::where(function($query) use ($account) {
            $query->where('asset_account_id', $account->id)
                  ->orWhere('depreciation_expense_account_id', $account->id)
                  ->orWhere('accumulated_depreciation_account_id', $account->id);
        })->count();
        if ($fixedAssetCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account that is used in fixed assets. Please delete or modify the fixed assets first.');
        }

        // Check if account is used in items (purchase, sales, or inventory account)
        $itemCount = \App\Models\Item::where(function($query) use ($account) {
            $query->where('purchase_account_id', $account->id)
                  ->orWhere('sales_account_id', $account->id)
                  ->orWhere('inventory_account_id', $account->id);
        })->count();
        if ($itemCount > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account that is used in inventory items. Please update the inventory items first.');
        }

        try {
            $account->delete();

            AuditLogService::log(
                'chart_of_accounts',
                'delete',
                "Deleted account {$account->code} - {$account->name}"
            );

            return redirect()->route('chart-of-accounts.index')
                ->with('success', 'Account deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Account Deletion Error', [
                'error' => $e->getMessage(),
                'account_id' => $account->id,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check for specific constraint violations
            if (str_contains($e->getMessage(), 'FOREIGN KEY constraint')) {
                return redirect()->route('chart-of-accounts.index')
                    ->with('error', 'Cannot delete account. It is being used by other records. Please check all related transactions and delete them first.');
            }

            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account. Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Account Deletion Error', [
                'error' => $e->getMessage(),
                'account_id' => $account->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'An error occurred while deleting the account: ' . $e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        $account = Account::findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);
        
        $status = $account->is_active ? 'activated' : 'deactivated';

        AuditLogService::log(
            'chart_of_accounts',
            'status_change',
            "Account {$account->code} - {$account->name} {$status}"
        );

        return redirect()->route('chart-of-accounts.index')
            ->with('success', "Account {$status} successfully.");
    }
}
