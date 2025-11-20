@extends('layouts.app')

@section('title', 'View Inventory Item')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $item->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">SKU: {{ $item->sku }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('inventory.index') }}" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Item Details</h3>
        </div>
        <dl class="divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">SKU</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $item->sku }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Item Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $item->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($item->type === 'product') bg-blue-100 text-blue-800
                            @else bg-purple-100 text-purple-800
                            @endif">
                            {{ ucfirst($item->type) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $item->category->name ?? '-' }}</dd>
                </div>
                @if($item->description)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $item->description }}</dd>
                </div>
                @endif
                @if($item->unit_of_measure)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Unit of Measure</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $item->unit_of_measure }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Standard Cost</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($item->standard_cost, 2) }}</dd>
                </div>
                @if($item->track_quantity)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Quantity on Hand</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->quantity_on_hand, 2) }} {{ $item->unit_of_measure }}</dd>
                </div>
                @if($item->reorder_point)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reorder Point</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($item->reorder_point, 2) }} {{ $item->unit_of_measure }}</dd>
                </div>
                @endif
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        @if($item->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $item->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Attachments (Links)</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($item->attachments)
                            @foreach(array_filter(explode("\n", $item->attachments)) as $link)
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
</div>
@endsection

