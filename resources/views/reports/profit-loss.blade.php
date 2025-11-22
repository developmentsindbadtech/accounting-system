@extends('layouts.app')

@section('title', 'Profit & Loss')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Profit & Loss Statement (IFRS Compliant)</h1>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->canEdit())
            <a href="{{ route('reports.profit-loss.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            @else
            <span class="bg-green-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50 text-sm" title="Viewer: No permission">
                Download CSV
            </span>
            @endif
            <form method="GET" action="{{ route('reports.profit-loss') }}" class="flex flex-wrap items-center gap-2">
                <label for="start_date" class="text-sm font-medium text-gray-700">From:</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? now()->startOfQuarter()->format('Y-m-d') }}" 
                    class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <label for="end_date" class="text-sm font-medium text-gray-700">To:</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? now()->endOfQuarter()->format('Y-m-d') }}" 
                    class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Update
                </button>
            </form>
            <a href="{{ route('reports.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Back to Reports
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-6 py-4">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Profit & Loss Statement</h2>
                <p class="text-sm text-gray-600">
                    For the period from {{ \Carbon\Carbon::parse($startDate ?? now()->startOfQuarter())->format('F d, Y') }} 
                    to {{ \Carbon\Carbon::parse($endDate ?? now()->endOfQuarter())->format('F d, Y') }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <!-- Revenue Section -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">REVENUE</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($revenueAccounts ?? [] as $account)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $account->code }} - {{ $account->name }}</td>
                                <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($account->period_balance ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No revenue accounts</td>
                            </tr>
                            @endforelse
                            <tr class="border-t-2 border-gray-400">
                                <td class="px-4 py-2 font-semibold text-gray-900">Total Revenue</td>
                                <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($revenueTotal ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Expenses Section -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">EXPENSES</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($expenseAccounts ?? [] as $account)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $account->code }} - {{ $account->name }}</td>
                                <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($account->period_balance ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No expense accounts</td>
                            </tr>
                            @endforelse
                            <tr class="border-t-2 border-gray-400">
                                <td class="px-4 py-2 font-semibold text-gray-900">Total Expenses</td>
                                <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($expenseTotal ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Net Income -->
            <div class="mt-8 border-t-4 border-gray-800 pt-4">
                <table class="min-w-full">
                    <tbody>
                        <tr>
                            <td class="px-4 py-2 text-lg font-bold text-gray-900">Net Income (Loss)</td>
                            <td class="px-4 py-2 text-lg font-bold font-mono text-right {{ ($netIncome ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                SAR {{ number_format($netIncome ?? 0, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
