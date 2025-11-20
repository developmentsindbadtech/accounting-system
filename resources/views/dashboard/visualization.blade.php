@extends('layouts.app')

@section('title', 'Data Visualization')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Visualization (IFRS Compliant)</h1>
            <p class="mt-1 text-sm text-gray-500">Comprehensive financial charts and graphs</p>
        </div>
        <a href="{{ route('dashboard', request()->all()) }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm">
            Back to Dashboard
        </a>
    </div>

    <!-- Filters Section -->
    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('dashboard.visualization') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                <a href="{{ route('dashboard.visualization') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 text-sm">
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

    <!-- Revenue Trend Chart -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Revenue Trend 
                @if($startDate && $endDate)
                    ({{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }})
                @else
                    (Last 12 Months)
                @endif
            </h2>
            <div class="h-96">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Profit/Loss Trend Chart -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Profit/Loss Trend 
                @if($startDate && $endDate)
                    ({{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }})
                @else
                    (Last 12 Months)
                @endif
            </h2>
            <div class="h-96">
                <canvas id="profitLossChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown Chart -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Expense Breakdown 
                @if($startDate && $endDate)
                    ({{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }})
                @else
                    (This Month)
                @endif
            </h2>
            <div class="h-96">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Accounts Receivable Aging Chart -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Accounts Receivable Aging</h2>
            <div class="h-96">
                <canvas id="arAgingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Accounts Payable Aging Chart -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Accounts Payable Aging</h2>
            <div class="h-96">
                <canvas id="apAgingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Composition -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Balance Sheet Composition (IFRS)</h2>
            <div class="h-96">
                <canvas id="balanceSheetChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Cash Flow Trend -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Cash Flow Trend 
                @if($startDate && $endDate)
                    ({{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }})
                @else
                    (Last 12 Months)
                @endif
            </h2>
            <div class="h-96">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($revenueTrends ?? []);
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(t => t.month),
            datasets: [{
                label: 'Revenue (SAR)',
                data: revenueData.map(t => t.revenue),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: SAR ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'SAR ' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });

    // Profit/Loss Trend Chart
    const profitLossCtx = document.getElementById('profitLossChart').getContext('2d');
    const profitData = @json($profitTrends ?? []);
    new Chart(profitLossCtx, {
        type: 'bar',
        data: {
            labels: profitData.map(t => t.month),
            datasets: [{
                label: 'Profit/Loss (SAR)',
                data: profitData.map(t => t.profit),
                backgroundColor: profitData.map(t => t.profit >= 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)'),
                borderColor: profitData.map(t => t.profit >= 0 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)'),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            return (value >= 0 ? 'Profit: ' : 'Loss: ') + 'SAR ' + Math.abs(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'SAR ' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });

    // Expense Breakdown Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    const expenseData = @json($expenseBreakdown ?? []);
    new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: expenseData.map(e => e.name),
            datasets: [{
                data: expenseData.map(e => e.amount),
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(147, 51, 234, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(14, 165, 233, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(251, 146, 60, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': SAR ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // AR Aging Chart
    const arAgingCtx = document.getElementById('arAgingChart').getContext('2d');
    const arAgingData = @json($arAging ?? []);
    new Chart(arAgingCtx, {
        type: 'bar',
        data: {
            labels: ['Current', '1-30 Days', '31-60 Days', '61-90 Days', '90+ Days'],
            datasets: [{
                label: 'Amount (SAR)',
                data: [
                    arAgingData.current || 0,
                    arAgingData.days_1_30 || 0,
                    arAgingData.days_31_60 || 0,
                    arAgingData.days_61_90 || 0,
                    arAgingData.days_90_plus || 0
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(185, 28, 28, 0.8)'
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(249, 115, 22)',
                    'rgb(239, 68, 68)',
                    'rgb(185, 28, 28)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: SAR ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'SAR ' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });

    // AP Aging Chart
    const apAgingCtx = document.getElementById('apAgingChart').getContext('2d');
    const apAgingData = @json($apAging ?? []);
    new Chart(apAgingCtx, {
        type: 'bar',
        data: {
            labels: ['Current', '1-30 Days', '31-60 Days', '61-90 Days', '90+ Days'],
            datasets: [{
                label: 'Amount (SAR)',
                data: [
                    apAgingData.current || 0,
                    apAgingData.days_1_30 || 0,
                    apAgingData.days_31_60 || 0,
                    apAgingData.days_61_90 || 0,
                    apAgingData.days_90_plus || 0
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(185, 28, 28, 0.8)'
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(249, 115, 22)',
                    'rgb(239, 68, 68)',
                    'rgb(185, 28, 28)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: SAR ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'SAR ' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });

    // Balance Sheet Chart
    const balanceSheetCtx = document.getElementById('balanceSheetChart').getContext('2d');
    const financialSummary = @json($financialSummary ?? []);
    new Chart(balanceSheetCtx, {
        type: 'bar',
        data: {
            labels: ['Current Assets', 'Non-Current Assets', 'Current Liabilities', 'Non-Current Liabilities', 'Equity'],
            datasets: [{
                label: 'Amount (SAR)',
                data: [
                    financialSummary.total_current_assets || 0,
                    financialSummary.total_non_current_assets || 0,
                    Math.abs(financialSummary.total_current_liabilities || 0),
                    Math.abs(financialSummary.total_non_current_liabilities || 0),
                    financialSummary.total_equity_with_retained || 0
                ],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(37, 99, 235, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(185, 28, 28, 0.8)',
                    'rgba(34, 197, 94, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(37, 99, 235)',
                    'rgb(239, 68, 68)',
                    'rgb(185, 28, 28)',
                    'rgb(34, 197, 94)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: SAR ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'SAR ' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });

    // Cash Flow Trend Chart
    const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
    const profitTrendsData = @json($profitTrends ?? []);
    new Chart(cashFlowCtx, {
        type: 'line',
        data: {
            labels: profitTrendsData.map(t => t.month),
            datasets: [{
                label: 'Revenue',
                data: profitTrendsData.map(t => t.revenue),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'Expenses',
                data: profitTrendsData.map(t => t.expenses),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'Profit/Loss',
                data: profitTrendsData.map(t => t.profit),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: false,
                borderDash: [5, 5]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': SAR ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'SAR ' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });
</script>
@endsection

