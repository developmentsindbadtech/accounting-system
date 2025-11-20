@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <div class="flex items-center space-x-3">
            <a href="{{ route('invoices.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            @if(auth()->user()->canEdit())
            <a href="{{ route('invoices.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Create New Invoice
            </a>
            @else
            <span class="bg-indigo-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50" title="Viewer: No permission">
                Create New Invoice
            </span>
            @endif
        </div>
    </div>
    
    <!-- Sort Controls -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex items-center space-x-4">
            <label for="sort_by" class="text-sm font-medium text-gray-700">Sort by:</label>
            <select name="sort_by" id="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="updated_at" {{ ($sortBy ?? 'updated_at') === 'updated_at' ? 'selected' : '' }}>Recently Updated</option>
                <option value="created_at" {{ ($sortBy ?? '') === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                <option value="invoice_number" {{ ($sortBy ?? '') === 'invoice_number' ? 'selected' : '' }}>Invoice Number</option>
                <option value="invoice_date" {{ ($sortBy ?? '') === 'invoice_date' ? 'selected' : '' }}>Invoice Date</option>
                <option value="due_date" {{ ($sortBy ?? '') === 'due_date' ? 'selected' : '' }}>Due Date</option>
                <option value="total" {{ ($sortBy ?? '') === 'total' ? 'selected' : '' }}>Total</option>
                <option value="status" {{ ($sortBy ?? '') === 'status' ? 'selected' : '' }}>Status</option>
            </select>
            <select name="sort_dir" id="sort_dir" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="desc" {{ ($sortDir ?? 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                <option value="asc" {{ ($sortDir ?? '') === 'asc' ? 'selected' : '' }}>Ascending</option>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                Sort
            </button>
        </form>
    </div>

    @php
        $actionBtnBase = 'inline-flex items-center px-3 py-1 text-xs font-semibold border rounded transition-colors duration-150';
    @endphp

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <x-sortable-header field="invoice_number" label="Invoice #" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="invoices.index" />
                        <x-sortable-header field="customer_id" label="Customer" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="invoices.index" />
                        <x-sortable-header field="invoice_date" label="Date" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="invoices.index" />
                        <x-sortable-header field="due_date" label="Due Date" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="invoices.index" />
                        <x-sortable-header field="total" label="Total" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="invoices.index" />
                        <x-sortable-header field="status" label="Status" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="invoices.index" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->customer->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->due_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">SAR {{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($invoice->status === 'paid') bg-green-100 text-green-800
                                @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                                @elseif($invoice->status === 'sent') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('invoices.show', $invoice->id) }}" class="{{ $actionBtnBase }} border-indigo-200 text-indigo-600 hover:bg-indigo-50">View</a>
                                @if(auth()->user()->canEdit())
                                @if($invoice->status === 'draft')
                                <form action="{{ route('invoices.send', $invoice->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $actionBtnBase }} border-blue-200 text-blue-600 hover:bg-blue-50" title="Send Invoice">Send</button>
                                </form>
                                @elseif($invoice->status === 'sent' || $invoice->status === 'overdue')
                                <form action="{{ route('invoices.mark-paid', $invoice->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $actionBtnBase }} border-green-200 text-green-600 hover:bg-green-50" title="Mark as Paid">Mark Paid</button>
                                </form>
                                @if($invoice->status !== 'paid')
                                <form action="{{ route('invoices.cancel', $invoice->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this invoice?');">
                                    @csrf
                                    <button type="submit" class="{{ $actionBtnBase }} border-orange-200 text-orange-600 hover:bg-orange-50" title="Cancel Invoice">Cancel</button>
                                </form>
                                @endif
                                @endif
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="{{ $actionBtnBase }} border-red-200 text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                                @else
                                @if($invoice->status === 'draft')
                                <span class="{{ $actionBtnBase }} border-blue-200 text-blue-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Send</span>
                                @elseif($invoice->status === 'sent' || $invoice->status === 'overdue')
                                <span class="{{ $actionBtnBase }} border-green-200 text-green-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Mark Paid</span>
                                @if($invoice->status !== 'paid')
                                <span class="{{ $actionBtnBase }} border-orange-200 text-orange-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Cancel</span>
                                @endif
                                @endif
                                <span class="{{ $actionBtnBase }} border-red-200 text-red-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Delete</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No invoices found.@if(auth()->user()->canEdit()) <a href="{{ route('invoices.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first invoice</a>.@endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($invoices->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <x-pagination-numeric :paginator="$invoices" label="invoices" />
        </div>
        @endif
    </div>
</div>
@endsection
