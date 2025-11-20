<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        // Get filter parameters
        $module = $request->get('module', 'all');
        $action = $request->get('action', 'all');
        $userId = $request->get('user_id', 'all');
        $search = $request->get('search');
        $dateRange = $request->get('date_range', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        // Build query
        $query = AuditLog::where('tenant_id', $tenantId);

        // Module filter
        if ($module && $module !== 'all') {
            $query->where('module', $module);
        }

        // Action filter
        if ($action && $action !== 'all') {
            $query->where('action', $action);
        }

        // User filter
        if ($userId && $userId !== 'all') {
            $query->where('user_id', $userId);
        }

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%");
            });
        }

        // Date range filter
        $this->applyDateRangeFilter($query, $dateRange, $dateFrom, $dateTo);

        // Sorting
        $allowedSorts = ['created_at', 'actor_name', 'module', 'action'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $logs = $query->paginate(30)->withQueryString();

        // Get filter options
        $modules = [
            'chart_of_accounts' => 'Chart of Accounts',
            'journal_entries' => 'Journal Entries',
            'customers' => 'Customers',
            'invoices' => 'Invoices',
            'vendors' => 'Vendors',
            'bills' => 'Bills',
            'inventory' => 'Inventory',
            'fixed_assets' => 'Fixed Assets',
            'glossary' => 'Glossary',
        ];

        $actions = [
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'status_change' => 'Status Changed',
            'post' => 'Posted',
            'reverse' => 'Reversed',
        ];

        // Get users who have performed actions
        $users = User::where('tenant_id', $tenantId)
            ->whereHas('auditLogs')
            ->orderBy('name')
            ->get();

        // Count active filters
        $activeFilters = 0;
        if ($module !== 'all') $activeFilters++;
        if ($action !== 'all') $activeFilters++;
        if ($userId !== 'all') $activeFilters++;
        if ($search) $activeFilters++;
        if ($dateRange !== 'all' || $dateFrom || $dateTo) $activeFilters++;

        return view('logs.index', compact(
            'logs', 
            'modules', 
            'actions',
            'users',
            'module', 
            'action',
            'userId',
            'search', 
            'dateRange',
            'dateFrom', 
            'dateTo',
            'sortBy',
            'sortDir',
            'activeFilters'
        ));
    }

    public function destroy(AuditLog $log)
    {
        $this->authorizeLogAccess($log);
        $log->delete();

        return redirect()->route('logs.index')
            ->with('success', 'Log entry deleted.');
    }

    public function export(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $module = $request->get('module', 'all');
        $action = $request->get('action', 'all');
        $userId = $request->get('user_id', 'all');
        $search = $request->get('search');
        $dateRange = $request->get('date_range', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = AuditLog::where('tenant_id', $tenantId);

        if ($module && $module !== 'all') {
            $query->where('module', $module);
        }

        if ($action && $action !== 'all') {
            $query->where('action', $action);
        }

        if ($userId && $userId !== 'all') {
            $query->where('user_id', $userId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $this->applyDateRangeFilter($query, $dateRange, $dateFrom, $dateTo);

        $logs = $query->orderByDesc('created_at')->get();

        $filename = 'audit-logs-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Timestamp', 'Actor Name', 'Actor Email', 'Module', 'Action', 'Description']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->actor_name ?? 'System',
                    $log->actor_email ?? '',
                    ucfirst(str_replace('_', ' ', $log->module)),
                    ucfirst(str_replace('_', ' ', $log->action)),
                    $log->description,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    protected function applyDateRangeFilter($query, $dateRange, $dateFrom, $dateTo)
    {
        if ($dateRange === 'custom' && $dateFrom && $dateTo) {
            $query->whereDate('created_at', '>=', $dateFrom)
                  ->whereDate('created_at', '<=', $dateTo);
        } elseif ($dateRange === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateRange === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($dateRange === 'last_7_days') {
            $query->whereDate('created_at', '>=', today()->subDays(7));
        } elseif ($dateRange === 'last_30_days') {
            $query->whereDate('created_at', '>=', today()->subDays(30));
        } elseif ($dateRange === 'this_month') {
            $query->whereYear('created_at', today()->year)
                  ->whereMonth('created_at', today()->month);
        } elseif ($dateRange === 'last_month') {
            $query->whereYear('created_at', today()->subMonth()->year)
                  ->whereMonth('created_at', today()->subMonth()->month);
        } elseif ($dateRange === 'this_year') {
            $query->whereYear('created_at', today()->year);
        }
    }

    protected function authorizeLogAccess(AuditLog $log): void
    {
        if ($log->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized to access this log.');
        }
    }
}
