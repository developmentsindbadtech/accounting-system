@extends('layouts.app')

@section('title', 'View Customer')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Customer Code: {{ $customer->code ?: 'N/A' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('customers.index') }}" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Customer Details</h3>
        </div>
        <dl class="divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Customer Code</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $customer->code ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->name }}</dd>
                </div>
                @if($customer->email)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->email }}</dd>
                </div>
                @endif
                @if($customer->phone)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->phone }}</dd>
                </div>
                @endif
                @if($customer->address)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->address }}</dd>
                </div>
                @endif
                @if($customer->tax_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tax ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->tax_id }}</dd>
                </div>
                @endif
                @if($customer->credit_limit)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Credit Limit</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($customer->credit_limit, 2) }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Current Balance</dt>
                    <dd class="mt-1 text-sm font-mono">
                        <span class="{{ $customer->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            SAR {{ number_format($customer->balance, 2) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        @if($customer->is_active)
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
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Attachments (Links)</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($customer->attachments)
                            @foreach(array_filter(explode("\n", $customer->attachments)) as $link)
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

    @if($customer->invoices->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Invoices</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customer->invoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900 font-mono">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->due_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($invoice->total, 2) }}</td>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

