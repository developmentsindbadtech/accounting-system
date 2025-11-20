<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\GeneralLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function trialBalance(Request $request)
    {
        // Disable caching to ensure fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $sortBy = $request->get('sort_by', 'code');
        $sortDir = $request->get('sort_dir', 'asc');
        $tenantId = auth()->user()->tenant_id;

        // Get all accounts with their balances
        $query = Account::where('tenant_id', $tenantId)
            ->where('is_active', true);
        
        $allowedSorts = ['code', 'name', 'type', 'balance'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'code';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'asc';
        
        $accounts = $query->orderBy($sortBy, $sortDir)->get();

        // Calculate balances from general ledger entries up to the date
        $balances = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereDate('entry_date', '<=', $asOfDate)
            ->select('account_id', DB::raw('SUM(debit - credit) as balance'))
            ->groupBy('account_id')
            ->pluck('balance', 'account_id');

        // Merge with opening balances
        foreach ($accounts as $account) {
            $account->calculated_balance = ($account->opening_balance ?? 0) + ($balances[$account->id] ?? 0);
        }
        
        // Re-sort by calculated balance if needed
        if ($sortBy === 'balance') {
            $accounts = $accounts->sortBy(function($account) use ($sortDir) {
                return $account->calculated_balance;
            }, SORT_REGULAR, $sortDir === 'desc');
        }

        return view('reports.trial-balance', compact('accounts', 'asOfDate', 'sortBy', 'sortDir'));
    }
    
    public function exportTrialBalance(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $sortBy = $request->get('sort_by', 'code');
        $sortDir = $request->get('sort_dir', 'asc');
        $tenantId = auth()->user()->tenant_id;

        $query = Account::where('tenant_id', $tenantId)
            ->where('is_active', true);
        
        $allowedSorts = ['code', 'name', 'type', 'balance'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'code';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'asc';
        
        $accounts = $query->orderBy($sortBy, $sortDir)->get();

        $balances = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereDate('entry_date', '<=', $asOfDate)
            ->select('account_id', DB::raw('SUM(debit - credit) as balance'))
            ->groupBy('account_id')
            ->pluck('balance', 'account_id');

        foreach ($accounts as $account) {
            $account->calculated_balance = ($account->opening_balance ?? 0) + ($balances[$account->id] ?? 0);
        }
        
        if ($sortBy === 'balance') {
            $accounts = $accounts->sortBy(function($account) use ($sortDir) {
                return $account->calculated_balance;
            }, SORT_REGULAR, $sortDir === 'desc');
        }
        
        $filename = 'trial-balance-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($accounts, $asOfDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Trial Balance as of ' . \Carbon\Carbon::parse($asOfDate)->format('F d, Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Account Code', 'Account Name', 'Type', 'Sub Type', 'Debit', 'Credit']);
            
            $totalDebit = 0;
            $totalCredit = 0;
            
            foreach ($accounts as $account) {
                $balance = $account->calculated_balance ?? 0;
                $debit = $balance > 0 ? number_format($balance, 2) : '';
                $credit = $balance < 0 ? number_format(abs($balance), 2) : '';
                
                if ($balance > 0) {
                    $totalDebit += $balance;
                } else {
                    $totalCredit += abs($balance);
                }
                
                fputcsv($file, [
                    $account->code,
                    $account->name,
                    $account->type,
                    $account->sub_type ?? '',
                    $debit,
                    $credit,
                ]);
            }
            
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', '', number_format($totalDebit, 2), number_format($totalCredit, 2)]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function profitLoss(Request $request)
    {
        // Disable caching to ensure fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Default to current quarter if no dates provided
        $now = now();
        $quarter = ceil($now->month / 3);
        $quarterStart = $now->copy()->month(($quarter - 1) * 3 + 1)->startOfMonth();
        $quarterEnd = $now->copy()->month($quarter * 3)->endOfMonth();
        
        $startDate = $request->get('start_date', $quarterStart->format('Y-m-d'));
        $endDate = $request->get('end_date', $quarterEnd->format('Y-m-d'));
        $tenantId = auth()->user()->tenant_id;

        // Get Revenue accounts (IFRS compliant)
        $revenueAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Revenue')
            ->where('is_active', true)
            ->get();

        // Get Expense accounts (IFRS compliant)
        $expenseAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Expense')
            ->where('is_active', true)
            ->get();

        // Calculate revenue totals
        $revenueTotal = 0;
        foreach ($revenueAccounts as $account) {
            $balance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->sum(DB::raw('credit - debit'));
            $account->period_balance = $balance;
            $revenueTotal += $balance;
        }

        // Calculate expense totals
        $expenseTotal = 0;
        foreach ($expenseAccounts as $account) {
            $balance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->sum(DB::raw('debit - credit'));
            $account->period_balance = $balance;
            $expenseTotal += $balance;
        }

        $netIncome = $revenueTotal - $expenseTotal;

        return view('reports.profit-loss', compact(
            'revenueAccounts',
            'expenseAccounts',
            'revenueTotal',
            'expenseTotal',
            'netIncome',
            'startDate',
            'endDate'
        ));
    }

    public function balanceSheet(Request $request)
    {
        // Disable caching to ensure fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Default to current quarter if no dates provided
        $now = now();
        $quarter = ceil($now->month / 3);
        $quarterStart = $now->copy()->month(($quarter - 1) * 3 + 1)->startOfMonth();
        $quarterEnd = $now->copy()->month($quarter * 3)->endOfMonth();
        
        $startDate = $request->get('start_date', $quarterStart->format('Y-m-d'));
        $endDate = $request->get('end_date', $quarterEnd->format('Y-m-d'));
        $tenantId = auth()->user()->tenant_id;

        // IFRS-compliant Balance Sheet structure
        // Check if sub_type column exists
        $hasSubType = DB::getSchemaBuilder()->hasColumn('accounts', 'sub_type');
        
        // Current Assets
        $currentAssetsQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $currentAssetsQuery->where('sub_type', 'Current Asset');
        } else {
            // Fallback: use code patterns if sub_type doesn't exist
            $currentAssetsQuery->where(function($q) {
                $q->where('code', 'like', '1%')
                  ->where('code', 'not like', '15%')
                  ->where('code', 'not like', '16%');
            });
        }
        $currentAssets = $currentAssetsQuery->orderBy('code')->get();

        // Non-Current Assets
        $nonCurrentAssetsQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $nonCurrentAssetsQuery->where('sub_type', 'Non-Current Asset');
        } else {
            // Fallback: use code patterns
            $nonCurrentAssetsQuery->where(function($q) {
                $q->where('code', 'like', '15%')
                  ->orWhere('code', 'like', '16%');
            });
        }
        $nonCurrentAssets = $nonCurrentAssetsQuery->orderBy('code')->get();

        // Current Liabilities
        $currentLiabilitiesQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $currentLiabilitiesQuery->where('sub_type', 'Current Liability');
        } else {
            // Fallback: use code patterns
            $currentLiabilitiesQuery->where('code', 'like', '2%');
        }
        $currentLiabilities = $currentLiabilitiesQuery->orderBy('code')->get();

        // Non-Current Liabilities
        $nonCurrentLiabilitiesQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $nonCurrentLiabilitiesQuery->where('sub_type', 'Non-Current Liability');
        } else {
            // Fallback: use code patterns
            $nonCurrentLiabilitiesQuery->where('code', 'like', '25%');
        }
        $nonCurrentLiabilities = $nonCurrentLiabilitiesQuery->orderBy('code')->get();

        // Equity
        $equityAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Equity')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Calculate balances for each account (up to end date)
        $calculateBalance = function ($account) use ($tenantId, $endDate) {
            $openingBalance = $account->opening_balance ?? 0;
            $entriesBalance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereDate('entry_date', '<=', $endDate)
                ->sum(DB::raw('debit - credit'));
            return $openingBalance + $entriesBalance;
        };

        foreach ($currentAssets as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($nonCurrentAssets as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($currentLiabilities as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($nonCurrentLiabilities as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($equityAccounts as $account) {
            $account->balance = $calculateBalance($account);
        }

        // Calculate totals
        $totalCurrentAssets = $currentAssets->sum('balance');
        $totalNonCurrentAssets = $nonCurrentAssets->sum('balance');
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $totalCurrentLiabilities = $currentLiabilities->sum('balance');
        $totalNonCurrentLiabilities = $nonCurrentLiabilities->sum('balance');
        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        $totalEquity = $equityAccounts->sum('balance');

        // Calculate retained earnings (Revenue - Expenses) for the period
        $revenueTotal = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function ($query) {
                $query->where('type', 'Revenue');
            })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum(DB::raw('credit - debit'));

        $expenseTotal = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function ($query) {
                $query->where('type', 'Expense');
            })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum(DB::raw('debit - credit'));

        $retainedEarnings = $revenueTotal - $expenseTotal;
        $totalEquityWithRetained = $totalEquity + $retainedEarnings;

        return view('reports.balance-sheet', compact(
            'currentAssets',
            'nonCurrentAssets',
            'currentLiabilities',
            'nonCurrentLiabilities',
            'equityAccounts',
            'totalCurrentAssets',
            'totalNonCurrentAssets',
            'totalAssets',
            'totalCurrentLiabilities',
            'totalNonCurrentLiabilities',
            'totalLiabilities',
            'totalEquity',
            'retainedEarnings',
            'totalEquityWithRetained',
            'startDate',
            'endDate'
        ));
    }
    
    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfQuarter()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfQuarter()->format('Y-m-d'));
        $tenantId = auth()->user()->tenant_id;

        $revenueAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Revenue')
            ->where('is_active', true)
            ->get();

        $expenseAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Expense')
            ->where('is_active', true)
            ->get();

        $revenueTotal = 0;
        foreach ($revenueAccounts as $account) {
            $balance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->sum(DB::raw('credit - debit'));
            $account->period_balance = $balance;
            $revenueTotal += $balance;
        }

        $expenseTotal = 0;
        foreach ($expenseAccounts as $account) {
            $balance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->sum(DB::raw('debit - credit'));
            $account->period_balance = $balance;
            $expenseTotal += $balance;
        }

        $netIncome = $revenueTotal - $expenseTotal;
        
        $filename = 'profit-loss-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($revenueAccounts, $expenseAccounts, $revenueTotal, $expenseTotal, $netIncome, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['PROFIT & LOSS STATEMENT']);
            fputcsv($file, ['Period: ' . \Carbon\Carbon::parse($startDate)->format('F d, Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('F d, Y')]);
            fputcsv($file, []);
            
            fputcsv($file, ['REVENUE']);
            fputcsv($file, ['Account Code', 'Account Name', 'Amount']);
            foreach ($revenueAccounts as $account) {
                if (($account->period_balance ?? 0) != 0) {
                    fputcsv($file, [
                        $account->code,
                        $account->name,
                        number_format($account->period_balance ?? 0, 2),
                    ]);
                }
            }
            fputcsv($file, ['TOTAL REVENUE', '', number_format($revenueTotal, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['EXPENSES']);
            fputcsv($file, ['Account Code', 'Account Name', 'Amount']);
            foreach ($expenseAccounts as $account) {
                if (($account->period_balance ?? 0) != 0) {
                    fputcsv($file, [
                        $account->code,
                        $account->name,
                        number_format($account->period_balance ?? 0, 2),
                    ]);
                }
            }
            fputcsv($file, ['TOTAL EXPENSES', '', number_format($expenseTotal, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['NET INCOME (LOSS)', '', number_format($netIncome, 2)]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    public function exportBalanceSheet(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfQuarter()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfQuarter()->format('Y-m-d'));
        $tenantId = auth()->user()->tenant_id;

        $hasSubType = DB::getSchemaBuilder()->hasColumn('accounts', 'sub_type');
        
        $currentAssetsQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $currentAssetsQuery->where('sub_type', 'Current Asset');
        } else {
            $currentAssetsQuery->where(function($q) {
                $q->where('code', 'like', '1%')
                  ->where('code', 'not like', '15%')
                  ->where('code', 'not like', '16%');
            });
        }
        $currentAssets = $currentAssetsQuery->orderBy('code')->get();

        $nonCurrentAssetsQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $nonCurrentAssetsQuery->where('sub_type', 'Non-Current Asset');
        } else {
            $nonCurrentAssetsQuery->where(function($q) {
                $q->where('code', 'like', '15%')
                  ->orWhere('code', 'like', '16%');
            });
        }
        $nonCurrentAssets = $nonCurrentAssetsQuery->orderBy('code')->get();

        $currentLiabilitiesQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $currentLiabilitiesQuery->where('sub_type', 'Current Liability');
        } else {
            $currentLiabilitiesQuery->where('code', 'like', '2%');
        }
        $currentLiabilities = $currentLiabilitiesQuery->orderBy('code')->get();

        $nonCurrentLiabilitiesQuery = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where('is_active', true);
        
        if ($hasSubType) {
            $nonCurrentLiabilitiesQuery->where('sub_type', 'Non-Current Liability');
        } else {
            $nonCurrentLiabilitiesQuery->where('code', 'like', '25%');
        }
        $nonCurrentLiabilities = $nonCurrentLiabilitiesQuery->orderBy('code')->get();

        $equityAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Equity')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $calculateBalance = function ($account) use ($tenantId, $endDate) {
            $openingBalance = $account->opening_balance ?? 0;
            $entriesBalance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereDate('entry_date', '<=', $endDate)
                ->sum(DB::raw('debit - credit'));
            return $openingBalance + $entriesBalance;
        };

        foreach ($currentAssets as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($nonCurrentAssets as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($currentLiabilities as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($nonCurrentLiabilities as $account) {
            $account->balance = $calculateBalance($account);
        }

        foreach ($equityAccounts as $account) {
            $account->balance = $calculateBalance($account);
        }

        $totalCurrentAssets = $currentAssets->sum('balance');
        $totalNonCurrentAssets = $nonCurrentAssets->sum('balance');
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $totalCurrentLiabilities = $currentLiabilities->sum('balance');
        $totalNonCurrentLiabilities = $nonCurrentLiabilities->sum('balance');
        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        $totalEquity = $equityAccounts->sum('balance');

        $revenueTotal = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function ($query) {
                $query->where('type', 'Revenue');
            })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum(DB::raw('credit - debit'));

        $expenseTotal = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function ($query) {
                $query->where('type', 'Expense');
            })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum(DB::raw('debit - credit'));

        $retainedEarnings = $revenueTotal - $expenseTotal;
        $totalEquityWithRetained = $totalEquity + $retainedEarnings;
        
        $filename = 'balance-sheet-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($currentAssets, $nonCurrentAssets, $currentLiabilities, $nonCurrentLiabilities, $equityAccounts, $totalCurrentAssets, $totalNonCurrentAssets, $totalAssets, $totalCurrentLiabilities, $totalNonCurrentLiabilities, $totalLiabilities, $totalEquity, $retainedEarnings, $totalEquityWithRetained, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['BALANCE SHEET']);
            fputcsv($file, ['Period: ' . \Carbon\Carbon::parse($startDate)->format('F d, Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('F d, Y')]);
            fputcsv($file, []);
            
            fputcsv($file, ['ASSETS']);
            fputcsv($file, ['Current Assets']);
            fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
            foreach ($currentAssets as $asset) {
                fputcsv($file, [
                    $asset->code,
                    $asset->name,
                    number_format($asset->balance ?? 0, 2),
                ]);
            }
            fputcsv($file, ['TOTAL CURRENT ASSETS', '', number_format($totalCurrentAssets, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['Non-Current Assets']);
            fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
            foreach ($nonCurrentAssets as $asset) {
                fputcsv($file, [
                    $asset->code,
                    $asset->name,
                    number_format($asset->balance ?? 0, 2),
                ]);
            }
            fputcsv($file, ['TOTAL NON-CURRENT ASSETS', '', number_format($totalNonCurrentAssets, 2)]);
            fputcsv($file, ['TOTAL ASSETS', '', number_format($totalAssets, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['LIABILITIES & EQUITY']);
            fputcsv($file, ['Current Liabilities']);
            fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
            foreach ($currentLiabilities as $liability) {
                fputcsv($file, [
                    $liability->code,
                    $liability->name,
                    number_format(abs($liability->balance ?? 0), 2),
                ]);
            }
            fputcsv($file, ['TOTAL CURRENT LIABILITIES', '', number_format($totalCurrentLiabilities, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['Non-Current Liabilities']);
            fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
            foreach ($nonCurrentLiabilities as $liability) {
                fputcsv($file, [
                    $liability->code,
                    $liability->name,
                    number_format(abs($liability->balance ?? 0), 2),
                ]);
            }
            fputcsv($file, ['TOTAL NON-CURRENT LIABILITIES', '', number_format($totalNonCurrentLiabilities, 2)]);
            fputcsv($file, ['TOTAL LIABILITIES', '', number_format($totalLiabilities, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['Equity']);
            fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
            foreach ($equityAccounts as $equity) {
                fputcsv($file, [
                    $equity->code,
                    $equity->name,
                    number_format($equity->balance ?? 0, 2),
                ]);
            }
            fputcsv($file, ['Retained Earnings', '', number_format($retainedEarnings, 2)]);
            fputcsv($file, ['TOTAL EQUITY', '', number_format($totalEquityWithRetained, 2)]);
            fputcsv($file, []);
            fputcsv($file, ['TOTAL LIABILITIES & EQUITY', '', number_format($totalLiabilities + $totalEquityWithRetained, 2)]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
