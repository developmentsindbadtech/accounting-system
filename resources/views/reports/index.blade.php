@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Financial Reports</h1>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('reports.trial-balance') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Trial Balance</h3>
                <p class="text-sm text-gray-500">View account balances and verify accounting equation</p>
            </div>
        </a>

        <a href="{{ route('reports.profit-loss') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Profit & Loss</h3>
                <p class="text-sm text-gray-500">Income statement showing revenue and expenses</p>
            </div>
        </a>

        <a href="{{ route('reports.balance-sheet') }}" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Balance Sheet</h3>
                <p class="text-sm text-gray-500">Assets, liabilities, and equity statement</p>
            </div>
        </a>
    </div>
</div>
@endsection

