@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Trail</h1>
            <p class="text-sm text-gray-600">Track every key action performed in the accounting system.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('logs.export', request()->query()) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download CSV
            </a>
        </div>
    </div>

    <!-- Active Filters Indicator -->
    @if($activeFilters > 0)
    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span class="text-sm font-medium text-blue-900">{{ $activeFilters }} active filter(s)</span>
            </div>
            <a href="{{ route('logs.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Clear All Filters</a>
        </div>
    </div>
    @endif

    <!-- Smart Filters -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('logs.index') }}" id="filterForm" class="space-y-4">
            <!-- First Row: Module, Action, User -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="module" class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                    <select name="module" id="module" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="all" {{ $module === 'all' ? 'selected' : '' }}>All Modules</option>
                        @foreach($modules as $value => $label)
                            <option value="{{ $value }}" {{ $module === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                    <select name="action" id="action" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="all" {{ $action === 'all' ? 'selected' : '' }}>All Actions</option>
                        @foreach($actions as $value => $label)
                            <option value="{{ $value }}" {{ $action === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="all" {{ $userId === 'all' ? 'selected' : '' }}>All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Second Row: Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select name="date_range" id="date_range" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="toggleCustomDateRange()">
                        <option value="all" {{ $dateRange === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $dateRange === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last_7_days" {{ $dateRange === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last_30_days" {{ $dateRange === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ $dateRange === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $dateRange === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $dateRange === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $dateRange === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div id="custom_date_from" style="display: {{ $dateRange === 'custom' ? 'block' : 'none' }};">
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div id="custom_date_to" style="display: {{ $dateRange === 'custom' ? 'block' : 'none' }};">
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <!-- Third Row: Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="Search by name, email, action, description, or module...">
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2">
                @if($activeFilters > 0)
                <a href="{{ route('logs.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Clear Filters
                </a>
                @endif
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div id="logs-list" class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('logs.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_dir' => $sortBy === 'created_at' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-1 hover:text-gray-700">
                                Timestamp
                                @if($sortBy === 'created_at')
                                    @if($sortDir === 'asc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('logs.index', array_merge(request()->query(), ['sort_by' => 'actor_name', 'sort_dir' => $sortBy === 'actor_name' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-1 hover:text-gray-700">
                                Actor
                                @if($sortBy === 'actor_name')
                                    @if($sortDir === 'asc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('logs.index', array_merge(request()->query(), ['sort_by' => 'module', 'sort_dir' => $sortBy === 'module' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-1 hover:text-gray-700">
                                Module
                                @if($sortBy === 'module')
                                    @if($sortDir === 'asc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('logs.index', array_merge(request()->query(), ['sort_by' => 'action', 'sort_dir' => $sortBy === 'action' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-1 hover:text-gray-700">
                                Action
                                @if($sortBy === 'action')
                                    @if($sortDir === 'asc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        @if(auth()->user()->canDeleteLog())
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="font-medium">{{ $log->actor_name ?? 'System' }}</div>
                            <div class="text-xs text-gray-500">{{ $log->actor_email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $modules[$log->module] ?? ucfirst(str_replace('_', ' ', $log->module)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $actionColors = [
                                    'create' => 'bg-green-100 text-green-800',
                                    'update' => 'bg-yellow-100 text-yellow-800',
                                    'delete' => 'bg-red-100 text-red-800',
                                    'status_change' => 'bg-purple-100 text-purple-800',
                                    'post' => 'bg-indigo-100 text-indigo-800',
                                    'reverse' => 'bg-orange-100 text-orange-800',
                                ];
                                $actionColor = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $actionColor }}">
                                {{ $actions[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->description }}</td>
                        @if(auth()->user()->canDeleteLog())
                        <td class="px-6 py-4 text-right text-sm">
                            <form method="POST" action="{{ route('logs.destroy', $log) }}" class="inline" onsubmit="return confirm('Delete this log entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 border border-red-500 text-red-600 rounded hover:bg-red-50 text-xs font-medium">
                                    Delete
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canDeleteLog() ? '6' : '5' }}" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium">No activity logs found.</p>
                                @if($activeFilters > 0)
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <x-pagination-numeric :paginator="$logs" label="log entries" anchor="logs-list" />
        </div>
        @endif
    </div>
</div>

<script>
function toggleCustomDateRange() {
    const dateRange = document.getElementById('date_range').value;
    const customDateFrom = document.getElementById('custom_date_from');
    const customDateTo = document.getElementById('custom_date_to');
    
    if (dateRange === 'custom') {
        customDateFrom.style.display = 'block';
        customDateTo.style.display = 'block';
    } else {
        customDateFrom.style.display = 'none';
        customDateTo.style.display = 'none';
    }
}

// Auto-submit on filter change (optional - can be removed if you prefer manual submit)
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const autoSubmit = false; // Set to true if you want auto-submit on change
    
    if (autoSubmit) {
        const filterInputs = filterForm.querySelectorAll('select, input[type="text"], input[type="date"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (input.id !== 'date_range') {
                    filterForm.submit();
                }
            });
        });
    }
});
</script>
@endsection

