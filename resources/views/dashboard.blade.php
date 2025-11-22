@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Financial Dashboard (IFRS Compliant)</h1>
            <p class="mt-1 text-sm text-gray-500">Real-time financial overview and key metrics</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard.visualization', request()->all()) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                Data Visualization
            </a>
            @if(auth()->user()->canEdit())
            <a href="{{ route('dashboard.export', request()->all()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            @else
            <span class="bg-green-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50 text-sm" title="Viewer: No permission">
                Download CSV
            </span>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-7">
            @if(auth()->user()->canEdit())
            <a href="{{ route('journal-entries.create') }}" class="bg-indigo-50 hover:bg-indigo-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-indigo-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-900">Journal Entry</p>
            </a>
            <a href="{{ route('invoices.create') }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-blue-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-900">New Invoice</p>
            </a>
            <a href="{{ route('bills.create') }}" class="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-orange-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-900">New Bill</p>
            </a>
            <a href="{{ route('customers.create') }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-green-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="text-sm font-medium text-gray-900">New Customer</p>
            </a>
            <a href="{{ route('vendors.create') }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-purple-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="text-sm font-medium text-gray-900">New Vendor</p>
            </a>
            @else
            <span class="bg-indigo-100 p-4 rounded-lg text-center cursor-not-allowed opacity-50" title="Viewer: No permission">
                <svg class="h-8 w-8 text-indigo-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-500">Journal Entry</p>
            </span>
            <span class="bg-blue-100 p-4 rounded-lg text-center cursor-not-allowed opacity-50" title="Viewer: No permission">
                <svg class="h-8 w-8 text-blue-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-500">New Invoice</p>
            </span>
            <span class="bg-orange-100 p-4 rounded-lg text-center cursor-not-allowed opacity-50" title="Viewer: No permission">
                <svg class="h-8 w-8 text-orange-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-500">New Bill</p>
            </span>
            <span class="bg-green-100 p-4 rounded-lg text-center cursor-not-allowed opacity-50" title="Viewer: No permission">
                <svg class="h-8 w-8 text-green-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="text-sm font-medium text-gray-500">New Customer</p>
            </span>
            <span class="bg-purple-100 p-4 rounded-lg text-center cursor-not-allowed opacity-50" title="Viewer: No permission">
                <svg class="h-8 w-8 text-purple-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="text-sm font-medium text-gray-500">New Vendor</p>
            </span>
            @endif
            <a href="{{ route('reports.index') }}" class="bg-gray-50 hover:bg-gray-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-gray-900">Reports</p>
            </a>
            <a href="{{ route('glossary.index') }}" class="bg-teal-50 hover:bg-teal-100 p-4 rounded-lg text-center transition-colors">
                <svg class="h-8 w-8 text-teal-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm font-medium text-gray-900">View Glossary</p>
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Period Filter -->
            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Fiscal Period</label>
                <select name="period" id="period" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="current_month" {{ $period === 'current_month' ? 'selected' : '' }}>Current Month</option>
                    <option value="current_quarter" {{ $period === 'current_quarter' ? 'selected' : '' }}>Current Quarter</option>
                    <option value="current_year" {{ $period === 'current_year' ? 'selected' : '' }}>Current Year</option>
                    <option value="ytd" {{ $period === 'ytd' ? 'selected' : '' }}>Year to Date</option>
                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_quarter" {{ $period === 'last_quarter' ? 'selected' : '' }}>Last Quarter</option>
                    <option value="last_year" {{ $period === 'last_year' ? 'selected' : '' }}>Last Year</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <!-- Start Date (shown when custom is selected) -->
            <div id="start-date-group" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>

            <!-- End Date (shown when custom is selected) -->
            <div id="end-date-group" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Account Type Filter -->
            <div>
                <label for="account_type" class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                <select name="account_type" id="account_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="all" {{ $accountType === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="asset" {{ $accountType === 'asset' ? 'selected' : '' }}>Assets</option>
                    <option value="liability" {{ $accountType === 'liability' ? 'selected' : '' }}>Liabilities</option>
                    <option value="equity" {{ $accountType === 'equity' ? 'selected' : '' }}>Equity</option>
                    <option value="revenue" {{ $accountType === 'revenue' ? 'selected' : '' }}>Revenue</option>
                    <option value="expense" {{ $accountType === 'expense' ? 'selected' : '' }}>Expenses</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="posted" {{ $status === 'posted' ? 'selected' : '' }}>Posted</option>
                    <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm w-full">
                    Apply Filters
                </button>
                <a href="{{ route('dashboard') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 text-sm">
                    Reset
                </a>
            </div>
        </form>
        <div class="mt-3 text-xs text-gray-500">
            Showing data for <span class="font-semibold">{{ $periodLabel ?? 'Selected Period' }}</span>
        </div>
    </div>

    <script>
        // Show/hide date inputs based on period selection
        document.getElementById('period').addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            document.getElementById('start-date-group').style.display = isCustom ? 'block' : 'none';
            document.getElementById('end-date-group').style.display = isCustom ? 'block' : 'none';
        });
    </script>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Cash Balance -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cash Balance</dt>
                            <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['cash_balance'] ?? 0, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Receivable -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Accounts Receivable</dt>
                            <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['accounts_receivable'] ?? 0, 2) }}</dd>
                            @if(($arAging['count'] ?? 0) > 0)
                            <dd class="text-xs text-red-600 mt-1">{{ $arAging['count'] }} overdue invoices</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Payable -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Accounts Payable</dt>
                            <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['accounts_payable'] ?? 0, 2) }}</dd>
                            @if(($apAging['count'] ?? 0) > 0)
                            <dd class="text-xs text-red-600 mt-1">{{ $apAging['count'] }} overdue bills</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Income YTD -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Net Income (YTD)</dt>
                            <dd class="text-lg font-medium {{ ($financialSummary['profit_ytd'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                SAR {{ number_format($financialSummary['profit_ytd'] ?? 0, 2) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue & Profit Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Revenue - Selected Period -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Revenue ({{ $periodLabel ?? 'Selected Period' }})</dt>
                    <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['revenue_this_month'] ?? 0, 2) }}</dd>
                    @if(($financialSummary['revenue_last_month'] ?? 0) > 0)
                    @php
                        $revenueChange = (($financialSummary['revenue_this_month'] ?? 0) - ($financialSummary['revenue_last_month'] ?? 0)) / ($financialSummary['revenue_last_month'] ?? 1) * 100;
                    @endphp
                    <dd class="text-xs {{ $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange, 1) }}% vs previous period
                    </dd>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Revenue YTD -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Revenue (YTD)</dt>
                    <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['revenue_ytd'] ?? 0, 2) }}</dd>
                </dl>
            </div>
        </div>

        <!-- Profit - Selected Period -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Profit ({{ $periodLabel ?? 'Selected Period' }})</dt>
                    <dd class="text-lg font-medium {{ ($financialSummary['profit_this_month'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        SAR {{ number_format($financialSummary['profit_this_month'] ?? 0, 2) }}
                    </dd>
                    @if(($financialSummary['profit_last_month'] ?? 0) != 0)
                    @php
                        $profitChange = (($financialSummary['profit_this_month'] ?? 0) - ($financialSummary['profit_last_month'] ?? 0)) / abs($financialSummary['profit_last_month'] ?? 1) * 100;
                    @endphp
                    <dd class="text-xs {{ $profitChange >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        {{ $profitChange >= 0 ? '+' : '' }}{{ number_format($profitChange, 1) }}% vs previous period
                    </dd>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Expenses - Selected Period -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Expenses ({{ $periodLabel ?? 'Selected Period' }})</dt>
                    <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['expenses_this_month'] ?? 0, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- IFRS Balance Sheet Summary -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
        <!-- Total Assets -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Assets</dt>
                    <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['total_assets'] ?? 0, 2) }}</dd>
                    <dd class="text-xs text-gray-500 mt-1">
                        Current: SAR {{ number_format($financialSummary['total_current_assets'] ?? 0, 2) }} | 
                        Non-Current: SAR {{ number_format($financialSummary['total_non_current_assets'] ?? 0, 2) }}
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Total Liabilities -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Liabilities</dt>
                    <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['total_liabilities'] ?? 0, 2) }}</dd>
                    <dd class="text-xs text-gray-500 mt-1">
                        Current: SAR {{ number_format($financialSummary['total_current_liabilities'] ?? 0, 2) }} | 
                        Non-Current: SAR {{ number_format($financialSummary['total_non_current_liabilities'] ?? 0, 2) }}
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Total Equity -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Equity</dt>
                    <dd class="text-lg font-medium text-gray-900">SAR {{ number_format($financialSummary['total_equity_with_retained'] ?? 0, 2) }}</dd>
                    <dd class="text-xs text-gray-500 mt-1">
                        Capital: SAR {{ number_format($financialSummary['total_equity'] ?? 0, 2) }} | 
                        Retained: SAR {{ number_format($financialSummary['retained_earnings'] ?? 0, 2) }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Accounts Receivable Aging -->
        <div id="recent-transactions" class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Accounts Receivable Aging</h2>
                @if(($arAging['total'] ?? 0) > 0)
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Current (Not Due)</span>
                        <span class="text-sm font-medium text-gray-900">SAR {{ number_format($arAging['current'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">1-30 Days</span>
                        <span class="text-sm font-medium {{ ($arAging['days_1_30'] ?? 0) > 0 ? 'text-yellow-600' : 'text-gray-900' }}">SAR {{ number_format($arAging['days_1_30'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">31-60 Days</span>
                        <span class="text-sm font-medium {{ ($arAging['days_31_60'] ?? 0) > 0 ? 'text-orange-600' : 'text-gray-900' }}">SAR {{ number_format($arAging['days_31_60'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">61-90 Days</span>
                        <span class="text-sm font-medium {{ ($arAging['days_61_90'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">SAR {{ number_format($arAging['days_61_90'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">90+ Days</span>
                        <span class="text-sm font-medium {{ ($arAging['days_90_plus'] ?? 0) > 0 ? 'text-red-700' : 'text-gray-900' }}">SAR {{ number_format($arAging['days_90_plus'] ?? 0, 2) }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-900">Total Outstanding</span>
                            <span class="text-sm font-bold text-gray-900">SAR {{ number_format($arAging['total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                @else
                <p class="text-sm text-gray-500">No outstanding receivables</p>
                @endif
            </div>
        </div>

        <!-- Accounts Payable Aging -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Accounts Payable Aging</h2>
                @if(($apAging['total'] ?? 0) > 0)
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Current (Not Due)</span>
                        <span class="text-sm font-medium text-gray-900">SAR {{ number_format($apAging['current'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">1-30 Days</span>
                        <span class="text-sm font-medium {{ ($apAging['days_1_30'] ?? 0) > 0 ? 'text-yellow-600' : 'text-gray-900' }}">SAR {{ number_format($apAging['days_1_30'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">31-60 Days</span>
                        <span class="text-sm font-medium {{ ($apAging['days_31_60'] ?? 0) > 0 ? 'text-orange-600' : 'text-gray-900' }}">SAR {{ number_format($apAging['days_31_60'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">61-90 Days</span>
                        <span class="text-sm font-medium {{ ($apAging['days_61_90'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">SAR {{ number_format($apAging['days_61_90'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">90+ Days</span>
                        <span class="text-sm font-medium {{ ($apAging['days_90_plus'] ?? 0) > 0 ? 'text-red-700' : 'text-gray-900' }}">SAR {{ number_format($apAging['days_90_plus'] ?? 0, 2) }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-900">Total Outstanding</span>
                            <span class="text-sm font-bold text-gray-900">SAR {{ number_format($apAging['total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                @else
                <p class="text-sm text-gray-500">No outstanding payables</p>
                @endif
            </div>
        </div>
    </div>


    <!-- Expense Breakdown & Overdue Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Expense Breakdown -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Expense Breakdown ({{ $periodLabel ?? 'Selected Period' }})</h2>
                @if(($expenseBreakdown ?? collect())->isNotEmpty())
                <div class="space-y-2">
                    @foreach($expenseBreakdown as $expense)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $expense['name'] }}</span>
                        <span class="text-sm font-medium text-gray-900">SAR {{ number_format($expense['amount'], 2) }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500">No expenses this month</p>
                @endif
            </div>
        </div>

        <!-- Overdue Invoices & Bills -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Overdue Items</h2>
                <div class="space-y-4">
                    @if(($overdueInvoices ?? collect())->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Overdue Invoices ({{ $overdueInvoices->count() }})</h3>
                        <div class="space-y-2">
                            @foreach($overdueInvoices->take(5) as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">{{ $item['invoice']->invoice_number }} - {{ $item['invoice']->customer->name ?? 'N/A' }}</span>
                                <span class="font-medium text-red-600">SAR {{ number_format($item['balance'], 2) }} ({{ $item['days_overdue'] }} days)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(($overdueBills ?? collect())->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Overdue Bills ({{ $overdueBills->count() }})</h3>
                        <div class="space-y-2">
                            @foreach($overdueBills->take(5) as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">{{ $item['bill']->bill_number }} - {{ $item['bill']->vendor->name ?? 'N/A' }}</span>
                                <span class="font-medium text-red-600">SAR {{ number_format($item['balance'], 2) }} ({{ $item['days_overdue'] }} days)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(($overdueInvoices ?? collect())->isEmpty() && ($overdueBills ?? collect())->isEmpty())
                    <p class="text-sm text-gray-500">No overdue items</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Recent Transactions -->
        <div id="recent-transactions" class="bg-white shadow rounded-lg">
            <div class="p-6">
                @php
                    // Calculate pagination variables first
                    $transactionsPagination = is_array($recentTransactions) ? $recentTransactions : [];
                    $txCurrent = $transactionsPagination['current_page'] ?? 1;
                    $txLast = $transactionsPagination['last_page'] ?? 1;
                    $txPerPage = $transactionsPagination['per_page'] ?? 30;
                    $txTotal = $transactionsPagination['total'] ?? 0;
                @endphp
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Recent Transactions</h2>
                    @if($txLast > 1)
                    @php
                        $resetQuery = request()->query();
                        unset($resetQuery['transactions_page']);
                    @endphp
                    <a href="{{ request()->url() . ($resetQuery ? '?' . http_build_query($resetQuery) : '') }}#recent-transactions" 
                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset View
                    </a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Account</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $transactionsData = is_array($recentTransactions) && isset($recentTransactions['data']) 
                                    ? $recentTransactions['data'] 
                                    : (is_object($recentTransactions) && isset($recentTransactions->data) 
                                        ? $recentTransactions->data 
                                        : ($recentTransactions ?? []));
                            @endphp
                            @forelse($transactionsData as $transaction)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    @php
                                        $date = $transaction['date'] ?? $transaction['created_at'] ?? now();
                                        if (!($date instanceof \Carbon\Carbon)) {
                                            try {
                                                $date = \Carbon\Carbon::parse($date);
                                            } catch (\Exception $e) {
                                                $date = now();
                                            }
                                        }
                                    @endphp
                                    {{ $date->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <div>{{ $transaction['account'] ?? 'N/A' }}</div>
                                    @if(isset($transaction['type']) && $transaction['type'] !== 'transaction' && isset($transaction['description']))
                                        <div class="text-xs text-gray-500">{{ Str::limit($transaction['description'], 40) }}</div>
                                    @elseif(isset($transaction['description']))
                                        <div class="text-xs text-gray-500">{{ Str::limit($transaction['description'], 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">
                                    @if(isset($transaction['debit']) && $transaction['debit'] > 0)
                                        <span class="text-green-600">+SAR {{ number_format($transaction['debit'], 2) }}</span>
                                    @elseif(isset($transaction['credit']) && $transaction['credit'] > 0)
                                        <span class="text-red-600">-SAR {{ number_format($transaction['credit'], 2) }}</span>
                                    @elseif(isset($transaction['amount']))
                                        @if($transaction['amount'] > 0)
                                            <span class="text-green-600">+SAR {{ number_format($transaction['amount'], 2) }}</span>
                                        @elseif($transaction['amount'] < 0)
                                            <span class="text-red-600">SAR {{ number_format($transaction['amount'], 2) }}</span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                    @if(isset($transaction['status']))
                                        <div class="text-xs mt-1">
                                            <span class="px-1.5 py-0.5 rounded text-xs
                                                @if($transaction['status'] === 'paid') bg-green-100 text-green-800
                                                @elseif($transaction['status'] === 'posted') bg-blue-100 text-blue-800
                                                @elseif($transaction['status'] === 'sent' || $transaction['status'] === 'received') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($transaction['status']) }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-sm text-gray-500 text-center">No recent transactions</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php
                    // Calculate pagination pages
                    $txPages = [];
                    if ($txLast <= 7) {
                        $txPages = range(1, $txLast);
                    } else {
                        $txPages[] = 1;
                        $start = max(2, $txCurrent - 2);
                        $end = min($txLast - 1, $txCurrent + 2);
                        if ($start > 2) { $txPages[] = '...'; }
                        for ($i = $start; $i <= $end; $i++) { $txPages[] = $i; }
                        if ($end < $txLast - 1) { $txPages[] = '...'; }
                        $txPages[] = $txLast;
                    }
                @endphp
                @if($txLast > 1)
                <div class="px-6 py-3 border-t border-gray-200">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-2 lg:space-y-0">
                        <div class="text-sm text-gray-700">
                            Showing {{ ($txPerPage * ($txCurrent - 1)) + 1 }} to 
                            {{ min($txPerPage * $txCurrent, $txTotal) }} 
                            of {{ $txTotal }} transactions
                        </div>
                        <div class="flex items-center space-x-1">
                            @foreach($txPages as $pageNumber)
                                @if($pageNumber === '...')
                                    <span class="px-2 py-1 text-sm text-gray-400">...</span>
                                @else
                                    <a href="{{ request()->fullUrlWithQuery(['transactions_page' => $pageNumber]) }}#recent-transactions"
                                       class="px-3 py-1 text-sm border rounded {{ $pageNumber == $txCurrent ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                        {{ $pageNumber }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Invoices & Bills -->
        <div id="recent-invoices-bills" class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Invoices & Bills</h2>
                <div class="space-y-4">
                    <div>
                        @php
                            // Calculate invoices pagination variables first
                            $invoicesPagination = is_array($recentInvoices) ? $recentInvoices : [];
                            $invCurrent = $invoicesPagination['current_page'] ?? 1;
                            $invLast = $invoicesPagination['last_page'] ?? 1;
                        @endphp
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Recent Invoices</h3>
                            @if($invLast > 1)
                            @php
                                $resetQuery = request()->query();
                                unset($resetQuery['invoices_page']);
                            @endphp
                            <a href="{{ request()->url() . ($resetQuery ? '?' . http_build_query($resetQuery) : '') }}#recent-invoices-bills" 
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset View
                            </a>
                            @endif
                        </div>
                        <div class="space-y-2">
                            @php
                                $invoicesData = is_array($recentInvoices) && isset($recentInvoices['data']) 
                                    ? $recentInvoices['data'] 
                                    : (is_object($recentInvoices) && isset($recentInvoices->data) 
                                        ? $recentInvoices->data 
                                        : ($recentInvoices ?? []));
                            @endphp
                            @forelse($invoicesData as $invoice)
                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    <span class="text-gray-600"> - {{ $invoice->customer->name ?? 'N/A' }}</span>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded
                                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                                            @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                                            @elseif($invoice->status === 'sent') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                        <span class="ml-2">{{ $invoice->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900">SAR {{ number_format($invoice->total, 2) }}</span>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">No recent invoices</p>
                            @endforelse
                        </div>
                        @php
                            // Calculate invoices pagination pages
                            $invPages = [];
                            if ($invLast <= 7) {
                                $invPages = range(1, $invLast);
                            } else {
                                $invPages[] = 1;
                                $start = max(2, $invCurrent - 2);
                                $end = min($invLast - 1, $invCurrent + 2);
                                if ($start > 2) { $invPages[] = '...'; }
                                for ($i = $start; $i <= $end; $i++) { $invPages[] = $i; }
                                if ($end < $invLast - 1) { $invPages[] = '...'; }
                                $invPages[] = $invLast;
                            }
                        @endphp
                        @if($invLast > 1)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-600">
                                    Page {{ $invCurrent }} of {{ $invLast }}
                                </div>
                                <div class="flex space-x-1">
                                    @foreach($invPages as $pageNumber)
                                        @if($pageNumber === '...')
                                            <span class="px-2 py-1 text-xs text-gray-400">...</span>
                                        @else
                                            <a href="{{ request()->fullUrlWithQuery(['invoices_page' => $pageNumber]) }}#recent-invoices-bills" 
                                               class="px-2 py-1 text-xs border rounded {{ $pageNumber == $invCurrent ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                                {{ $pageNumber }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div>
                        @php
                            // Calculate bills pagination variables first
                            $billsPagination = is_array($recentBills) ? $recentBills : [];
                            $billCurrent = $billsPagination['current_page'] ?? 1;
                            $billLast = $billsPagination['last_page'] ?? 1;
                        @endphp
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Recent Bills</h3>
                            @if($billLast > 1)
                            @php
                                $resetQuery = request()->query();
                                unset($resetQuery['bills_page']);
                            @endphp
                            <a href="{{ request()->url() . ($resetQuery ? '?' . http_build_query($resetQuery) : '') }}#recent-invoices-bills" 
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset View
                            </a>
                            @endif
                        </div>
                        <div class="space-y-2">
                            @php
                                $billsData = is_array($recentBills) && isset($recentBills['data']) 
                                    ? $recentBills['data'] 
                                    : (is_object($recentBills) && isset($recentBills->data) 
                                        ? $recentBills->data 
                                        : ($recentBills ?? []));
                            @endphp
                            @forelse($billsData as $bill)
                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <a href="{{ route('bills.show', $bill->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $bill->bill_number }}
                                    </a>
                                    <span class="text-gray-600"> - {{ $bill->vendor->name ?? 'N/A' }}</span>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded
                                            @if($bill->status === 'paid') bg-green-100 text-green-800
                                            @elseif($bill->status === 'overdue') bg-red-100 text-red-800
                                            @elseif($bill->status === 'received') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                        <span class="ml-2">{{ $bill->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900">SAR {{ number_format($bill->total, 2) }}</span>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">No recent bills</p>
                            @endforelse
                        </div>
                        @php
                            // Calculate bills pagination pages
                            $billPages = [];
                            if ($billLast <= 7) {
                                $billPages = range(1, $billLast);
                            } else {
                                $billPages[] = 1;
                                $start = max(2, $billCurrent - 2);
                                $end = min($billLast - 1, $billCurrent + 2);
                                if ($start > 2) { $billPages[] = '...'; }
                                for ($i = $start; $i <= $end; $i++) { $billPages[] = $i; }
                                if ($end < $billLast - 1) { $billPages[] = '...'; }
                                $billPages[] = $billLast;
                            }
                        @endphp
                        @if($billLast > 1)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-600">
                                    Page {{ $billCurrent }} of {{ $billLast }}
                                </div>
                                <div class="flex space-x-1">
                                    @foreach($billPages as $pageNumber)
                                        @if($pageNumber === '...')
                                            <span class="px-2 py-1 text-xs text-gray-400">...</span>
                                        @else
                                            <a href="{{ request()->fullUrlWithQuery(['bills_page' => $pageNumber]) }}#recent-invoices-bills" 
                                               class="px-2 py-1 text-xs border rounded {{ $pageNumber == $billCurrent ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                                {{ $pageNumber }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
