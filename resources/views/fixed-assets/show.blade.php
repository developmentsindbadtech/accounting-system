@extends('layouts.app')

@section('title', 'View Fixed Asset')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $asset->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Asset #: {{ $asset->asset_number }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('fixed-assets.index') }}" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Asset Details</h3>
        </div>
        <dl class="divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Asset Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $asset->asset_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Asset Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->category->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($asset->status === 'active') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($asset->status) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Purchase Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->purchase_date->format('M d, Y') }}</dd>
                </div>
                @if($asset->disposal_date)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Disposal Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->disposal_date->format('M d, Y') }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Purchase Cost</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($asset->purchase_cost, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Salvage Value</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($asset->salvage_value, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Useful Life</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->useful_life_years }} years</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Depreciation Method</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('-', ' ', $asset->depreciation_method)) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Accumulated Depreciation</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($asset->accumulated_depreciation, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Net Book Value</dt>
                    <dd class="mt-1 text-lg font-bold font-mono text-gray-900">SAR {{ number_format($asset->net_book_value, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Asset Account</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $asset->assetAccount->code ?? 'N/A' }} - {{ $asset->assetAccount->name ?? 'N/A' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Depreciation Expense Account</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $asset->depreciationExpenseAccount->code ?? 'N/A' }} - {{ $asset->depreciationExpenseAccount->name ?? 'N/A' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Accumulated Depreciation Account</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $asset->accumulatedDepreciationAccount->code ?? 'N/A' }} - {{ $asset->accumulatedDepreciationAccount->name ?? 'N/A' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $asset->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Attachments (Links)</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($asset->attachments)
                            @foreach(array_filter(explode("\n", $asset->attachments)) as $link)
                                <a href="{{ trim($link) }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-900 break-all block mb-1">
                                    {{ trim($link) }}
                                </a>
                            @endforeach
                        @else
                            <span class="text-gray-400 italic">No attachments</span>
                        @endif
                    </dd>
                </div>
            </div>
        </dl>
    </div>

    @if($asset->depreciationEntries->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Depreciation Entries</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Depreciation Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Accumulated Depreciation</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Book Value</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($asset->depreciationEntries as $entry)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->entry_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($entry->depreciation_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($entry->accumulated_depreciation, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($entry->net_book_value, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

