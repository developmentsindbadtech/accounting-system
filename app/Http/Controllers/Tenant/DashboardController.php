<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\JournalEntry;
use App\Models\GeneralLedgerEntry;
use App\Models\FixedAsset;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Disable caching to ensure fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $tenantId = auth()->user()->tenant_id;
        
        // Get filter parameters
        $period = $request->get('period', 'current_month'); // current_month, current_quarter, current_year, last_month, last_quarter, last_year, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $accountType = $request->get('account_type', 'all'); // all, asset, liability, equity, revenue, expense
        $status = $request->get('status', 'all'); // all, draft, posted, paid, sent, received, etc.
        
        // Calculate date range based on period
        $now = Carbon::now();
        $dateRange = $this->calculateDateRange($period, $startDate, $endDate, $now);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $periodLabel = $this->getPeriodLabel($period, $startDate, $endDate);
        
        // Financial Summary - IFRS Compliant (calculated from GL entries - always fresh)
        $financialSummary = $this->getFinancialSummary($tenantId, $now, $startDate, $endDate, $accountType);
        
        // Accounts Receivable Aging (as of period end date)
        $arAging = $this->getAccountsReceivableAging($tenantId, $endDate ?? $now);
        
        // Accounts Payable Aging (as of period end date)
        $apAging = $this->getAccountsPayableAging($tenantId, $endDate ?? $now);
        
        // Revenue Trends (Last 12 months)
        $revenueTrends = $this->getRevenueTrends($tenantId, $now, $startDate, $endDate);
        
        // Profit/Loss Trends (Last 12 months)
        $profitTrends = $this->getProfitTrends($tenantId, $now, $startDate, $endDate);
        
        // Expense Breakdown
        $expenseBreakdown = $this->getExpenseBreakdown($tenantId, $now, $startDate, $endDate);
        
        // Recent Activity (with status filter and pagination)
        $transactionsPage = $request->get('transactions_page', 1);
        $invoicesPage = $request->get('invoices_page', 1);
        $billsPage = $request->get('bills_page', 1);
        $recentTransactions = $this->getRecentTransactions($tenantId, $status, $transactionsPage);
        $recentInvoices = $this->getRecentInvoices($tenantId, $status, $invoicesPage);
        $recentBills = $this->getRecentBills($tenantId, $status, $billsPage);
        $recentJournalEntries = $this->getRecentJournalEntries($tenantId, $status);
        
        // Overdue Items (as of period end date)
        $overdueInvoices = $this->getOverdueInvoices($tenantId, $endDate ?? $now);
        $overdueBills = $this->getOverdueBills($tenantId, $endDate ?? $now);
        
        return view('dashboard', compact(
            'financialSummary',
            'arAging',
            'apAging',
            'revenueTrends',
            'profitTrends',
            'expenseBreakdown',
            'recentTransactions',
            'recentInvoices',
            'recentBills',
            'recentJournalEntries',
            'overdueInvoices',
            'overdueBills',
            'period',
            'startDate',
            'endDate',
            'accountType',
            'status',
            'periodLabel'
        ));
    }
    
    public function export()
    {
        $tenantId = auth()->user()->tenant_id;
        $now = Carbon::now();
        
        $financialSummary = $this->getFinancialSummary($tenantId, $now);
        $recentTransactions = $this->getRecentTransactions($tenantId);
        $recentInvoices = $this->getRecentInvoices($tenantId);
        $recentBills = $this->getRecentBills($tenantId);
        
        $filename = 'dashboard-export-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($financialSummary, $recentTransactions, $recentInvoices, $recentBills) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Financial Summary
            fputcsv($file, ['DASHBOARD EXPORT - ' . date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['FINANCIAL SUMMARY']);
            fputcsv($file, ['Cash Balance', number_format($financialSummary['cash_balance'] ?? 0, 2)]);
            fputcsv($file, ['Accounts Receivable', number_format($financialSummary['accounts_receivable'] ?? 0, 2)]);
            fputcsv($file, ['Accounts Payable', number_format($financialSummary['accounts_payable'] ?? 0, 2)]);
            fputcsv($file, ['Revenue (This Month)', number_format($financialSummary['revenue_this_month'] ?? 0, 2)]);
            fputcsv($file, ['Revenue (YTD)', number_format($financialSummary['revenue_ytd'] ?? 0, 2)]);
            fputcsv($file, ['Expenses (This Month)', number_format($financialSummary['expenses_this_month'] ?? 0, 2)]);
            fputcsv($file, ['Expenses (YTD)', number_format($financialSummary['expenses_ytd'] ?? 0, 2)]);
            fputcsv($file, ['Profit (This Month)', number_format($financialSummary['profit_this_month'] ?? 0, 2)]);
            fputcsv($file, ['Profit (YTD)', number_format($financialSummary['profit_ytd'] ?? 0, 2)]);
            fputcsv($file, ['Total Assets', number_format($financialSummary['total_assets'] ?? 0, 2)]);
            fputcsv($file, ['Total Liabilities', number_format($financialSummary['total_liabilities'] ?? 0, 2)]);
            fputcsv($file, ['Total Equity', number_format($financialSummary['total_equity_with_retained'] ?? 0, 2)]);
            fputcsv($file, []);
            
            // Recent Transactions
            fputcsv($file, ['RECENT TRANSACTIONS']);
            fputcsv($file, ['Date', 'Account', 'Description', 'Amount', 'Type', 'Status']);
            foreach ($recentTransactions->take(20) as $transaction) {
                $date = $transaction['date'] ?? $transaction['created_at'] ?? now();
                if (!($date instanceof \Carbon\Carbon)) {
                    $date = \Carbon\Carbon::parse($date);
                }
                fputcsv($file, [
                    $date->format('Y-m-d'),
                    $transaction['account'] ?? 'N/A',
                    $transaction['description'] ?? '',
                    isset($transaction['amount']) && $transaction['amount'] != 0 ? number_format($transaction['amount'], 2) : (isset($transaction['debit']) && $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : (isset($transaction['credit']) && $transaction['credit'] > 0 ? '-' . number_format($transaction['credit'], 2) : '')),
                    $transaction['type'] ?? 'transaction',
                    $transaction['status'] ?? '',
                ]);
            }
            fputcsv($file, []);
            
            // Recent Invoices
            fputcsv($file, ['RECENT INVOICES']);
            fputcsv($file, ['Invoice Number', 'Customer', 'Date', 'Due Date', 'Total', 'Status']);
            foreach ($recentInvoices->take(20) as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->customer->name ?? 'N/A',
                    $invoice->invoice_date->format('Y-m-d'),
                    $invoice->due_date->format('Y-m-d'),
                    number_format($invoice->total, 2),
                    ucfirst($invoice->status),
                ]);
            }
            fputcsv($file, []);
            
            // Recent Bills
            fputcsv($file, ['RECENT BILLS']);
            fputcsv($file, ['Bill Number', 'Vendor', 'Date', 'Due Date', 'Total', 'Status']);
            foreach ($recentBills->take(20) as $bill) {
                fputcsv($file, [
                    $bill->bill_number,
                    $bill->vendor->name ?? 'N/A',
                    $bill->bill_date->format('Y-m-d'),
                    $bill->due_date->format('Y-m-d'),
                    number_format($bill->total, 2),
                    ucfirst($bill->status),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function visualization(Request $request)
    {
        // Disable caching to ensure fresh data
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $tenantId = auth()->user()->tenant_id;
        
        // Get filter parameters
        $period = $request->get('period', 'current_month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $accountType = $request->get('account_type', 'all');
        $status = $request->get('status', 'all');
        
        // Calculate date range based on period
        $now = Carbon::now();
        $dateRange = $this->calculateDateRange($period, $startDate, $endDate, $now);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $periodLabel = $this->getPeriodLabel($period, $startDate, $endDate);
        
        // Get all the same data as the dashboard (calculated from GL entries - always fresh)
        $financialSummary = $this->getFinancialSummary($tenantId, $now, $startDate, $endDate, $accountType);
        $arAging = $this->getAccountsReceivableAging($tenantId, $endDate ?? $now);
        $apAging = $this->getAccountsPayableAging($tenantId, $endDate ?? $now);
        $revenueTrends = $this->getRevenueTrends($tenantId, $now, $startDate, $endDate);
        $profitTrends = $this->getProfitTrends($tenantId, $now, $startDate, $endDate);
        $expenseBreakdown = $this->getExpenseBreakdown($tenantId, $now, $startDate, $endDate);
        $recentTransactions = $this->getRecentTransactions($tenantId, $status);
        $recentInvoices = $this->getRecentInvoices($tenantId, $status);
        $recentBills = $this->getRecentBills($tenantId, $status);
        $overdueInvoices = $this->getOverdueInvoices($tenantId, $endDate ?? $now);
        $overdueBills = $this->getOverdueBills($tenantId, $endDate ?? $now);
        
        return view('dashboard.visualization', compact(
            'financialSummary',
            'arAging',
            'apAging',
            'revenueTrends',
            'profitTrends',
            'expenseBreakdown',
            'recentTransactions',
            'recentInvoices',
            'recentBills',
            'overdueInvoices',
            'overdueBills',
            'period',
            'startDate',
            'endDate',
            'accountType',
            'status',
            'periodLabel'
        ));
    }

    protected function calculateDateRange($period, $startDate, $endDate, $now)
    {
        switch ($period) {
            case 'current_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'current_quarter':
                $quarter = ceil($now->month / 3);
                return [
                    'start' => $now->copy()->month(($quarter - 1) * 3 + 1)->startOfMonth(),
                    'end' => $now->copy()->month($quarter * 3)->endOfMonth()
                ];
            case 'current_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            case 'last_quarter':
                $quarter = ceil($now->month / 3);
                $lastQuarter = $quarter - 1;
                if ($lastQuarter < 1) {
                    $lastQuarter = 4;
                    $year = $now->year - 1;
                } else {
                    $year = $now->year;
                }
                return [
                    'start' => Carbon::create($year, ($lastQuarter - 1) * 3 + 1, 1)->startOfMonth(),
                    'end' => Carbon::create($year, $lastQuarter * 3, 1)->endOfMonth()
                ];
            case 'last_year':
                return [
                    'start' => $now->copy()->subYear()->startOfYear(),
                    'end' => $now->copy()->subYear()->endOfYear()
                ];
            case 'ytd':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now
                ];
            case 'custom':
                return [
                    'start' => $startDate ? Carbon::parse($startDate) : $now->copy()->startOfMonth(),
                    'end' => $endDate ? Carbon::parse($endDate) : $now->copy()->endOfMonth()
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    protected function getPeriodLabel($period, $startDate, $endDate)
    {
        $start = $startDate ? $startDate->copy() : null;
        $end = $endDate ? $endDate->copy() : null;

        switch ($period) {
            case 'current_month':
                return $start ? $start->format('F Y') : 'Current Month';
            case 'current_quarter':
                return $start && $end ? $start->format('M d, Y') . ' - ' . $end->format('M d, Y') : 'Current Quarter';
            case 'current_year':
                return $start ? 'FY ' . $start->format('Y') : 'Current Year';
            case 'ytd':
                return $start && $end ? 'YTD ' . $start->format('Y') : 'Year to Date';
            case 'last_month':
                return $start ? 'Last Month (' . $start->format('M Y') . ')' : 'Last Month';
            case 'last_quarter':
                return $start && $end ? 'Last Quarter (' . $start->format('M d') . ' - ' . $end->format('M d, Y') . ')' : 'Last Quarter';
            case 'last_year':
                return $start ? 'FY ' . $start->format('Y') : 'Last Year';
            case 'custom':
                return $start && $end ? $start->format('M d, Y') . ' - ' . $end->format('M d, Y') : 'Custom Range';
            default:
                return $start && $end ? $start->format('M d, Y') . ' - ' . $end->format('M d, Y') : 'Selected Period';
        }
    }

    protected function getFinancialSummary($tenantId, $now, $startDate = null, $endDate = null, $accountType = 'all')
    {
        // Use end date for balance calculations (as of the end of selected period)
        $asOfDate = $endDate ?? $now;
        if (!($asOfDate instanceof Carbon)) {
            $asOfDate = Carbon::parse($asOfDate);
        }
        
        // Calculate balances from General Ledger Entries up to the selected period end date
        $calculateAccountBalance = function($account) use ($tenantId, $asOfDate) {
            $openingBalance = $account->opening_balance ?? 0;
            $entriesBalance = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->where('account_id', $account->id)
                ->whereDate('entry_date', '<=', $asOfDate->format('Y-m-d'))
                ->sum(DB::raw('debit - credit'));
            return $openingBalance + $entriesBalance;
        };

        // Cash Balance (Current Assets - Cash accounts) - calculated from GL
        $cashAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where(function($query) {
                $query->where('code', 'like', '1000%')
                      ->orWhere('name', 'like', '%Cash%')
                      ->orWhere('name', 'like', '%Bank%');
            })
            ->where('is_active', true)
            ->get();
        $cashBalance = $cashAccounts->sum($calculateAccountBalance);

        // Accounts Receivable - calculated from GL
        $arAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where(function($query) {
                $query->where('code', 'like', '1100%')
                      ->orWhere('name', 'like', '%Receivable%');
            })
            ->where('is_active', true)
            ->get();
        $arBalance = $arAccounts->sum($calculateAccountBalance);

        // Accounts Payable - calculated from GL
        $apAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where(function($query) {
                $query->where('code', 'like', '2000%')
                      ->orWhere('name', 'like', '%Payable%');
            })
            ->where('is_active', true)
            ->get();
        $apBalance = abs($apAccounts->sum($calculateAccountBalance));

        // Selected period calculations
        $periodStart = ($startDate ?? $now->copy()->startOfMonth())->copy()->startOfDay();
        $periodEnd = ($endDate ?? $now->copy()->endOfMonth())->copy()->endOfDay();
        $periodDays = max(1, $periodStart->diffInDays($periodEnd) + 1);
        $previousPeriodEnd = $periodStart->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodDays - 1)->startOfDay();

        // Revenue for selected period
        $revenueThisMonth = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function($query) {
                $query->where('type', 'Revenue');
            })
            ->whereBetween('entry_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum(DB::raw('credit - debit'));

        // Revenue previous period
        $revenueLastMonth = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function($query) {
                $query->where('type', 'Revenue');
            })
            ->whereBetween('entry_date', [$previousPeriodStart->toDateString(), $previousPeriodEnd->toDateString()])
            ->sum(DB::raw('credit - debit'));

        // Revenue YTD
        $revenueYTD = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function($query) {
                $query->where('type', 'Revenue');
            })
            ->whereYear('entry_date', $now->year)
            ->sum(DB::raw('credit - debit'));

        // Expenses for selected period
        $expensesThisMonth = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function($query) {
                $query->where('type', 'Expense');
            })
            ->whereBetween('entry_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum(DB::raw('debit - credit'));

        // Expenses previous period
        $expensesLastMonth = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function($query) {
                $query->where('type', 'Expense');
            })
            ->whereBetween('entry_date', [$previousPeriodStart->toDateString(), $previousPeriodEnd->toDateString()])
            ->sum(DB::raw('debit - credit'));

        // Expenses YTD
        $expensesYTD = GeneralLedgerEntry::where('tenant_id', $tenantId)
            ->whereHas('account', function($query) {
                $query->where('type', 'Expense');
            })
            ->whereYear('entry_date', $now->year)
            ->sum(DB::raw('debit - credit'));

        // Profit for selected period
        $profitThisMonth = $revenueThisMonth - $expensesThisMonth;
        $profitLastMonth = $revenueLastMonth - $expensesLastMonth;
        $profitYTD = $revenueYTD - $expensesYTD;

        // Total Assets (IFRS - Current + Non-Current) - calculated from GL
        $currentAssetAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where(function($query) {
                if (DB::getSchemaBuilder()->hasColumn('accounts', 'sub_type')) {
                    $query->where('sub_type', 'Current Asset');
                } else {
                    $query->where('code', 'like', '1%')
                          ->where('code', 'not like', '15%')
                          ->where('code', 'not like', '16%');
                }
            })
            ->where('is_active', true)
            ->get();
        $totalCurrentAssets = $currentAssetAccounts->sum($calculateAccountBalance);

        $nonCurrentAssetAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Asset')
            ->where(function($query) {
                if (DB::getSchemaBuilder()->hasColumn('accounts', 'sub_type')) {
                    $query->where('sub_type', 'Non-Current Asset');
                } else {
                    $query->where(function($q) {
                        $q->where('code', 'like', '15%')
                          ->orWhere('code', 'like', '16%');
                    });
                }
            })
            ->where('is_active', true)
            ->get();
        $totalNonCurrentAssets = $nonCurrentAssetAccounts->sum($calculateAccountBalance);

        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        // Total Liabilities (IFRS - Current + Non-Current) - calculated from GL
        $currentLiabilityAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where(function($query) {
                if (DB::getSchemaBuilder()->hasColumn('accounts', 'sub_type')) {
                    $query->where('sub_type', 'Current Liability');
                } else {
                    $query->where('code', 'like', '2%');
                }
            })
            ->where('is_active', true)
            ->get();
        $totalCurrentLiabilities = abs($currentLiabilityAccounts->sum($calculateAccountBalance));

        $nonCurrentLiabilityAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Liability')
            ->where(function($query) {
                if (DB::getSchemaBuilder()->hasColumn('accounts', 'sub_type')) {
                    $query->where('sub_type', 'Non-Current Liability');
                } else {
                    $query->where('code', 'like', '25%');
                }
            })
            ->where('is_active', true)
            ->get();
        $totalNonCurrentLiabilities = abs($nonCurrentLiabilityAccounts->sum($calculateAccountBalance));

        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        // Total Equity - calculated from GL
        $equityAccounts = Account::where('tenant_id', $tenantId)
            ->where('type', 'Equity')
            ->where('is_active', true)
            ->get();
        $totalEquity = $equityAccounts->sum($calculateAccountBalance);

        // Retained Earnings (Revenue - Expenses)
        $retainedEarnings = $revenueYTD - $expensesYTD;
        $totalEquityWithRetained = $totalEquity + $retainedEarnings;

        return [
            'cash_balance' => $cashBalance,
            'accounts_receivable' => $arBalance,
            'accounts_payable' => $apBalance,
            'revenue_this_month' => $revenueThisMonth,
            'revenue_last_month' => $revenueLastMonth,
            'revenue_ytd' => $revenueYTD,
            'expenses_this_month' => $expensesThisMonth,
            'expenses_last_month' => $expensesLastMonth,
            'expenses_ytd' => $expensesYTD,
            'profit_this_month' => $profitThisMonth,
            'profit_last_month' => $profitLastMonth,
            'profit_ytd' => $profitYTD,
            'total_current_assets' => $totalCurrentAssets,
            'total_non_current_assets' => $totalNonCurrentAssets,
            'total_assets' => $totalAssets,
            'total_current_liabilities' => $totalCurrentLiabilities,
            'total_non_current_liabilities' => $totalNonCurrentLiabilities,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'retained_earnings' => $retainedEarnings,
            'total_equity_with_retained' => $totalEquityWithRetained,
        ];
    }

    protected function getAccountsReceivableAging($tenantId, $asOfDate)
    {
        // Ensure asOfDate is Carbon instance
        if (!($asOfDate instanceof Carbon)) {
            $asOfDate = Carbon::parse($asOfDate);
        }
        
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['sent', 'overdue'])
            ->where('invoice_date', '<=', $asOfDate->format('Y-m-d'))
            ->with('customer')
            ->get();

        $aging = [
            'current' => 0,      // Not due yet
            'days_1_30' => 0,   // 1-30 days overdue
            'days_31_60' => 0,  // 31-60 days overdue
            'days_61_90' => 0,  // 61-90 days overdue
            'days_90_plus' => 0, // 90+ days overdue
            'total' => 0,
            'count' => 0,
        ];

        foreach ($invoices as $invoice) {
            $daysPastDue = $asOfDate->diffInDays($invoice->due_date, false);
            // Use balance_due if available, otherwise calculate
            $balance = $invoice->balance_due ?? ($invoice->total - ($invoice->amount_paid ?? 0));
            
            if ($balance > 0) {
                $aging['total'] += $balance;
                $aging['count']++;
                
                if ($daysPastDue < 0) {
                    // Overdue
                    $daysOverdue = abs($daysPastDue);
                    if ($daysOverdue <= 30) {
                        $aging['days_1_30'] += $balance;
                    } elseif ($daysOverdue <= 60) {
                        $aging['days_31_60'] += $balance;
                    } elseif ($daysOverdue <= 90) {
                        $aging['days_61_90'] += $balance;
                    } else {
                        $aging['days_90_plus'] += $balance;
                    }
                } else {
                    // Current (not due yet)
                    $aging['current'] += $balance;
                }
            }
        }

        return $aging;
    }

    protected function getAccountsPayableAging($tenantId, $asOfDate)
    {
        // Ensure asOfDate is Carbon instance
        if (!($asOfDate instanceof Carbon)) {
            $asOfDate = Carbon::parse($asOfDate);
        }
        
        $bills = Bill::where('tenant_id', $tenantId)
            ->whereIn('status', ['received', 'overdue'])
            ->where('bill_date', '<=', $asOfDate->format('Y-m-d'))
            ->with('vendor')
            ->get();

        $aging = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_90_plus' => 0,
            'total' => 0,
            'count' => 0,
        ];

        foreach ($bills as $bill) {
            $daysPastDue = $asOfDate->diffInDays($bill->due_date, false);
            // Use balance_due if available, otherwise calculate
            $balance = $bill->balance_due ?? ($bill->total - ($bill->amount_paid ?? 0));
            
            if ($balance > 0) {
                $aging['total'] += $balance;
                $aging['count']++;
                
                if ($daysPastDue < 0) {
                    // Overdue
                    $daysOverdue = abs($daysPastDue);
                    if ($daysOverdue <= 30) {
                        $aging['days_1_30'] += $balance;
                    } elseif ($daysOverdue <= 60) {
                        $aging['days_31_60'] += $balance;
                    } elseif ($daysOverdue <= 90) {
                        $aging['days_61_90'] += $balance;
                    } else {
                        $aging['days_90_plus'] += $balance;
                    }
                } else {
                    // Current
                    $aging['current'] += $balance;
                }
            }
        }

        return $aging;
    }

    protected function getRevenueTrends($tenantId, $now, $startDate = null, $endDate = null)
    {
        $trends = [];
        
        // Ensure dates are Carbon instances
        if ($startDate && !($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate && !($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }
        
        // If date range is provided, use it; otherwise default to last 12 months
        if ($startDate && $endDate) {
            $current = $startDate->copy();
            $end = $endDate->copy();
            
            // Determine interval based on date range
            $daysDiff = $current->diffInDays($end);
            
            if ($daysDiff <= 31) {
                // Daily intervals for short ranges
                while ($current <= $end) {
                    $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Revenue');
                        })
                        ->whereDate('entry_date', $current->format('Y-m-d'))
                        ->sum(DB::raw('credit - debit'));
                    
                    $trends[] = [
                        'month' => $current->format('M d'),
                        'revenue' => $revenue,
                    ];
                    $current->addDay();
                }
            } elseif ($daysDiff <= 365) {
                // Monthly intervals
                while ($current <= $end) {
                    $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Revenue');
                        })
                        ->whereMonth('entry_date', $current->month)
                        ->whereYear('entry_date', $current->year)
                        ->sum(DB::raw('credit - debit'));
                    
                    $trends[] = [
                        'month' => $current->format('M Y'),
                        'revenue' => $revenue,
                    ];
                    $current->addMonth();
                }
            } else {
                // Quarterly intervals for long ranges
                while ($current <= $end) {
                    $quarterEnd = $current->copy()->endOfQuarter();
                    if ($quarterEnd > $end) {
                        $quarterEnd = $end->copy();
                    }
                    
                    $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Revenue');
                        })
                        ->whereBetween('entry_date', [$current->format('Y-m-d'), $quarterEnd->format('Y-m-d')])
                        ->sum(DB::raw('credit - debit'));
                    
                    $trends[] = [
                        'month' => 'Q' . ceil($current->month / 3) . ' ' . $current->format('Y'),
                        'revenue' => $revenue,
                    ];
                    $current = $quarterEnd->copy()->addDay()->startOfQuarter();
                }
            }
        } else {
            // Default: Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                    ->whereHas('account', function($query) {
                        $query->where('type', 'Revenue');
                    })
                    ->whereMonth('entry_date', $date->month)
                    ->whereYear('entry_date', $date->year)
                    ->sum(DB::raw('credit - debit'));
                
                $trends[] = [
                    'month' => $date->format('M Y'),
                    'revenue' => $revenue,
                ];
            }
        }
        
        return $trends;
    }

    protected function getProfitTrends($tenantId, $now, $startDate = null, $endDate = null)
    {
        $trends = [];
        
        // Ensure dates are Carbon instances
        if ($startDate && !($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate && !($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }
        
        // If date range is provided, use it; otherwise default to last 12 months
        if ($startDate && $endDate) {
            $current = $startDate->copy();
            $end = $endDate->copy();
            
            // Determine interval based on date range
            $daysDiff = $current->diffInDays($end);
            
            if ($daysDiff <= 31) {
                // Daily intervals for short ranges
                while ($current <= $end) {
                    $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Revenue');
                        })
                        ->whereDate('entry_date', $current->format('Y-m-d'))
                        ->sum(DB::raw('credit - debit'));
                    
                    $expenses = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Expense');
                        })
                        ->whereDate('entry_date', $current->format('Y-m-d'))
                        ->sum(DB::raw('debit - credit'));
                    
                    $trends[] = [
                        'month' => $current->format('M d'),
                        'revenue' => $revenue,
                        'expenses' => $expenses,
                        'profit' => $revenue - $expenses,
                    ];
                    $current->addDay();
                }
            } elseif ($daysDiff <= 365) {
                // Monthly intervals
                while ($current <= $end) {
                    $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Revenue');
                        })
                        ->whereMonth('entry_date', $current->month)
                        ->whereYear('entry_date', $current->year)
                        ->sum(DB::raw('credit - debit'));
                    
                    $expenses = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Expense');
                        })
                        ->whereMonth('entry_date', $current->month)
                        ->whereYear('entry_date', $current->year)
                        ->sum(DB::raw('debit - credit'));
                    
                    $trends[] = [
                        'month' => $current->format('M Y'),
                        'revenue' => $revenue,
                        'expenses' => $expenses,
                        'profit' => $revenue - $expenses,
                    ];
                    $current->addMonth();
                }
            } else {
                // Quarterly intervals for long ranges
                while ($current <= $end) {
                    $quarterEnd = $current->copy()->endOfQuarter();
                    if ($quarterEnd > $end) {
                        $quarterEnd = $end->copy();
                    }
                    
                    $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Revenue');
                        })
                        ->whereBetween('entry_date', [$current->format('Y-m-d'), $quarterEnd->format('Y-m-d')])
                        ->sum(DB::raw('credit - debit'));
                    
                    $expenses = GeneralLedgerEntry::where('tenant_id', $tenantId)
                        ->whereHas('account', function($query) {
                            $query->where('type', 'Expense');
                        })
                        ->whereBetween('entry_date', [$current->format('Y-m-d'), $quarterEnd->format('Y-m-d')])
                        ->sum(DB::raw('debit - credit'));
                    
                    $trends[] = [
                        'month' => 'Q' . ceil($current->month / 3) . ' ' . $current->format('Y'),
                        'revenue' => $revenue,
                        'expenses' => $expenses,
                        'profit' => $revenue - $expenses,
                    ];
                    $current = $quarterEnd->copy()->addDay()->startOfQuarter();
                }
            }
        } else {
            // Default: Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $revenue = GeneralLedgerEntry::where('tenant_id', $tenantId)
                    ->whereHas('account', function($query) {
                        $query->where('type', 'Revenue');
                    })
                    ->whereMonth('entry_date', $date->month)
                    ->whereYear('entry_date', $date->year)
                    ->sum(DB::raw('credit - debit'));
                
                $expenses = GeneralLedgerEntry::where('tenant_id', $tenantId)
                    ->whereHas('account', function($query) {
                        $query->where('type', 'Expense');
                    })
                    ->whereMonth('entry_date', $date->month)
                    ->whereYear('entry_date', $date->year)
                    ->sum(DB::raw('debit - credit'));
                
                $trends[] = [
                    'month' => $date->format('M Y'),
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                    'profit' => $revenue - $expenses,
                ];
            }
        }
        
        return $trends;
    }

    protected function getExpenseBreakdown($tenantId, $now, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? $now->copy()->startOfMonth();
        $endDate = $endDate ?? $now->copy()->endOfMonth();
        
        $expenses = Account::where('tenant_id', $tenantId)
            ->where('type', 'Expense')
            ->where('is_active', true)
            ->get()
            ->map(function($account) use ($tenantId, $startDate, $endDate) {
                $query = GeneralLedgerEntry::where('tenant_id', $tenantId)
                    ->where('account_id', $account->id);
                
                if ($startDate && $endDate) {
                    $query->whereBetween('entry_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                }
                
                $amount = $query->sum(DB::raw('debit - credit'));
                
                return [
                    'name' => $account->name,
                    'amount' => $amount,
                ];
            })
            ->filter(function($item) {
                return $item['amount'] > 0;
            })
            ->sortByDesc('amount')
            ->take(10)
            ->values();

        return $expenses;
    }

    protected function getRecentTransactions($tenantId, $status = 'all', $page = 1)
    {
        try {
            // Get recent General Ledger entries with pagination (30 per page)
            $perPage = 30;
            $glEntries = GeneralLedgerEntry::where('tenant_id', $tenantId)
                ->with('account')
                ->orderBy('entry_date', 'desc')
                ->orderBy('id', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function($entry) {
                    $createdAt = $entry->created_at ?? $entry->entry_date ?? now();
                    if (!($createdAt instanceof \Carbon\Carbon)) {
                        $createdAt = \Carbon\Carbon::parse($createdAt);
                    }
                    
                    return [
                        'date' => $entry->entry_date ?? now(),
                        'type' => 'transaction',
                        'description' => $entry->description ?? 'Transaction',
                        'account' => $entry->account->name ?? 'N/A',
                        'debit' => (float)($entry->debit ?? 0),
                        'credit' => (float)($entry->credit ?? 0),
                        'amount' => $entry->debit > 0 ? (float)$entry->debit : -(float)($entry->credit ?? 0),
                        'created_at' => $createdAt,
                    ];
                });

            // Get recent invoice status changes (including when marked as paid)
            $invoiceQuery = Invoice::where('tenant_id', $tenantId);
            if ($status !== 'all') {
                $invoiceQuery->where('status', $status);
            }
            $recentInvoices = $invoiceQuery
                ->with('customer')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($invoice) {
                    $updatedAt = $invoice->updated_at ?? $invoice->created_at ?? $invoice->invoice_date ?? now();
                    if (!($updatedAt instanceof \Carbon\Carbon)) {
                        $updatedAt = \Carbon\Carbon::parse($updatedAt);
                    }
                    
                    return [
                        'date' => $updatedAt,
                        'type' => 'invoice',
                        'description' => "Invoice {$invoice->invoice_number} - " . ($invoice->customer->name ?? 'N/A'),
                        'account' => 'Accounts Receivable',
                        'debit' => 0,
                        'credit' => 0,
                        'amount' => $invoice->status === 'paid' ? -(float)($invoice->total ?? 0) : 0,
                        'status' => $invoice->status,
                        'created_at' => $updatedAt,
                    ];
                });

            // Get recent bill status changes (including when marked as paid)
            $recentBills = Bill::where('tenant_id', $tenantId)
                ->with('vendor')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($bill) {
                    $updatedAt = $bill->updated_at ?? $bill->created_at ?? $bill->bill_date ?? now();
                    if (!($updatedAt instanceof \Carbon\Carbon)) {
                        $updatedAt = \Carbon\Carbon::parse($updatedAt);
                    }
                    
                    return [
                        'date' => $updatedAt,
                        'type' => 'bill',
                        'description' => "Bill {$bill->bill_number} - " . ($bill->vendor->name ?? 'N/A'),
                        'account' => 'Accounts Payable',
                        'debit' => 0,
                        'credit' => 0,
                        'amount' => $bill->status === 'paid' ? (float)($bill->total ?? 0) : 0,
                        'status' => $bill->status,
                        'created_at' => $updatedAt,
                    ];
                });

            // Get recent journal entries
            $recentJournalEntries = JournalEntry::where('tenant_id', $tenantId)
                ->orderBy('entry_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($entry) {
                    $createdAt = $entry->created_at ?? $entry->entry_date ?? now();
                    if (!($createdAt instanceof \Carbon\Carbon)) {
                        $createdAt = \Carbon\Carbon::parse($createdAt);
                    }
                    
                    return [
                        'date' => $entry->entry_date ?? now(),
                        'type' => 'journal',
                        'description' => "Journal Entry {$entry->entry_number}: " . ($entry->description ?? 'Entry'),
                        'account' => 'Multiple Accounts',
                        'debit' => (float)($entry->total_debit ?? 0),
                        'credit' => (float)($entry->total_credit ?? 0),
                        'amount' => 0,
                        'status' => $entry->status,
                        'created_at' => $createdAt,
                    ];
                });

            // Combine all transactions and sort by date
            $allTransactions = $glEntries
                ->concat($recentInvoices)
                ->concat($recentBills)
                ->concat($recentJournalEntries)
                ->filter(function($item) {
                    return isset($item['created_at']) && $item['created_at'] instanceof \Carbon\Carbon;
                })
                ->sortByDesc(function($item) {
                    return $item['created_at']->timestamp;
                })
                ->values();

            // Get total count for pagination (approximate - using GL entries count as base)
            $totalCount = GeneralLedgerEntry::where('tenant_id', $tenantId)->count();
            $perPage = 30;
            $totalPages = ceil($totalCount / $perPage);
            
            // Apply pagination to the combined results (30 per page)
            $paginatedTransactions = $allTransactions
                ->slice(($page - 1) * $perPage, $perPage)
                ->values()
                ->toArray();

            return [
                'data' => $paginatedTransactions,
                'current_page' => (int)$page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => $totalPages,
                'has_more' => $page < $totalPages,
            ];
        } catch (\Exception $e) {
            \Log::error('Dashboard getRecentTransactions Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Return empty collection on error
            return collect([]);
        }
    }

    protected function getRecentInvoices($tenantId, $status = 'all', $page = 1)
    {
        $perPage = 15;
        $query = Invoice::where('tenant_id', $tenantId)
            ->with('customer')
            ->orderBy('updated_at', 'desc')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $totalCount = $query->count();
        $invoices = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        
        return [
            'data' => $invoices,
            'current_page' => (int)$page,
            'per_page' => $perPage,
            'total' => $totalCount,
            'last_page' => ceil($totalCount / $perPage),
            'has_more' => $page < ceil($totalCount / $perPage),
        ];
    }

    protected function getRecentBills($tenantId, $status = 'all', $page = 1)
    {
        $perPage = 15;
        $query = Bill::where('tenant_id', $tenantId)
            ->with('vendor')
            ->orderBy('updated_at', 'desc')
            ->orderBy('bill_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $totalCount = $query->count();
        $bills = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        
        return [
            'data' => $bills,
            'current_page' => (int)$page,
            'per_page' => $perPage,
            'total' => $totalCount,
            'last_page' => ceil($totalCount / $perPage),
            'has_more' => $page < ceil($totalCount / $perPage),
        ];
    }

    protected function getRecentJournalEntries($tenantId, $status = 'all')
    {
        $query = JournalEntry::where('tenant_id', $tenantId)
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        return $query->limit(10)->get();
    }

    protected function getOverdueInvoices($tenantId, $asOfDate)
    {
        // Ensure asOfDate is Carbon instance
        if (!($asOfDate instanceof Carbon)) {
            $asOfDate = Carbon::parse($asOfDate);
        }
        
        return Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<', $asOfDate->toDateString())
            ->where('invoice_date', '<=', $asOfDate->format('Y-m-d'))
            ->with('customer')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function($invoice) use ($asOfDate) {
                $balance = $invoice->balance_due ?? ($invoice->total - ($invoice->amount_paid ?? 0));
                $daysOverdue = round($asOfDate->diffInDays($invoice->due_date));
                return [
                    'invoice' => $invoice,
                    'balance' => $balance,
                    'days_overdue' => $daysOverdue,
                ];
            });
    }

    protected function getOverdueBills($tenantId, $asOfDate)
    {
        // Ensure asOfDate is Carbon instance
        if (!($asOfDate instanceof Carbon)) {
            $asOfDate = Carbon::parse($asOfDate);
        }
        
        return Bill::where('tenant_id', $tenantId)
            ->whereIn('status', ['received', 'overdue'])
            ->where('due_date', '<', $asOfDate->toDateString())
            ->where('bill_date', '<=', $asOfDate->format('Y-m-d'))
            ->with('vendor')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function($bill) use ($asOfDate) {
                $balance = $bill->balance_due ?? ($bill->total - ($bill->amount_paid ?? 0));
                $daysOverdue = round($asOfDate->diffInDays($bill->due_date));
                return [
                    'bill' => $bill,
                    'balance' => $balance,
                    'days_overdue' => $daysOverdue,
                ];
            });
    }
}
