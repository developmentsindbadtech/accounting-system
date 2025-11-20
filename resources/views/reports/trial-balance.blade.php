@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Trial Balance (IFRS Compliant)</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.trial-balance.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            <form method="GET" action="{{ route('reports.trial-balance') }}" class="flex items-center space-x-2">
                <label for="as_of_date" class="text-sm font-medium text-gray-700">As of Date:</label>
                <input type="date" name="as_of_date" id="as_of_date" value="{{ $asOfDate ?? now()->format('Y-m-d') }}" 
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
    
    <!-- Sort Controls -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('reports.trial-balance') }}" class="flex items-center space-x-4">
            <input type="hidden" name="as_of_date" value="{{ $asOfDate ?? now()->format('Y-m-d') }}">
            <label for="sort_by" class="text-sm font-medium text-gray-700">Sort by:</label>
            <select name="sort_by" id="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="code" {{ ($sortBy ?? 'code') === 'code' ? 'selected' : '' }}>Account Code</option>
                <option value="name" {{ ($sortBy ?? '') === 'name' ? 'selected' : '' }}>Account Name</option>
                <option value="type" {{ ($sortBy ?? '') === 'type' ? 'selected' : '' }}>Type</option>
                <option value="balance" {{ ($sortBy ?? '') === 'balance' ? 'selected' : '' }}>Balance</option>
            </select>
            <select name="sort_dir" id="sort_dir" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="asc" {{ ($sortDir ?? 'asc') === 'asc' ? 'selected' : '' }}>Ascending</option>
                <option value="desc" {{ ($sortDir ?? '') === 'desc' ? 'selected' : '' }}>Descending</option>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                Sort
            </button>
        </form>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-6 py-4">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Trial Balance</h2>
                <p class="text-sm text-gray-600">As of {{ \Carbon\Carbon::parse($asOfDate ?? now())->format('F d, Y') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <x-sortable-header field="code" label="Account Code" :currentSort="$sortBy ?? 'code'" :currentDir="$sortDir ?? 'asc'" route="reports.trial-balance" :params="['as_of_date' => $asOfDate ?? now()->format('Y-m-d')]" />
                            <x-sortable-header field="name" label="Account Name" :currentSort="$sortBy ?? 'code'" :currentDir="$sortDir ?? 'asc'" route="reports.trial-balance" :params="['as_of_date' => $asOfDate ?? now()->format('Y-m-d')]" />
                            <x-sortable-header field="type" label="Type" :currentSort="$sortBy ?? 'code'" :currentDir="$sortDir ?? 'asc'" route="reports.trial-balance" :params="['as_of_date' => $asOfDate ?? now()->format('Y-m-d')]" />
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        @forelse($accounts ?? [] as $account)
                        @php
                            $balance = $account->calculated_balance ?? 0;
                            if ($balance > 0) {
                                $totalDebit += $balance;
                            } else {
                                $totalCredit += abs($balance);
                            }
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $account->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $account->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $account->type }} @if($account->sub_type) ({{ $account->sub_type }}) @endif</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">
                                @if($balance > 0) SAR {{ number_format($balance, 2) }} @else - @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">
                                @if($balance < 0) SAR {{ number_format(abs($balance), 2) }} @else - @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No accounts found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="border-t-2 border-gray-400">
                            <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900">TOTAL</td>
                            <td class="px-6 py-4 text-sm font-bold font-mono text-right text-gray-900">SAR {{ number_format($totalDebit, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-bold font-mono text-right text-gray-900">SAR {{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if(abs($totalDebit - $totalCredit) > 0.01)
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">Warning: Trial Balance is not balanced! Difference: SAR {{ number_format(abs($totalDebit - $totalCredit), 2) }}</span>
            </div>
            @else
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">Trial Balance is balanced.</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
