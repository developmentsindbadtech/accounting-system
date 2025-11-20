@extends('layouts.app')

@section('title', 'View Invoice')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1>
            <p class="mt-1 text-sm text-gray-500">Customer: {{ $invoice->customer->name ?? 'N/A' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('invoices.index') }}" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Invoice Details</h3>
        </div>
        <dl class="divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $invoice->invoice_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Customer</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('customers.show', $invoice->customer_id) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $invoice->customer->name ?? 'N/A' }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Invoice Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->invoice_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                            @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                            @elseif($invoice->status === 'sent') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </dd>
                </div>
                @if($invoice->reference)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->reference }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Subtotal</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($invoice->subtotal, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">VAT Amount</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($invoice->vat_amount, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total</dt>
                    <dd class="mt-1 text-lg font-bold font-mono text-gray-900">SAR {{ number_format($invoice->total, 2) }}</dd>
                </div>
                @if($invoice->amount_paid)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount Paid</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($invoice->amount_paid, 2) }}</dd>
                </div>
                @endif
                @if($invoice->balance_due)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Balance Due</dt>
                    <dd class="mt-1 text-sm font-mono text-red-600">SAR {{ number_format($invoice->balance_due, 2) }}</dd>
                </div>
                @endif
                @if($invoice->notes)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->notes }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->createdBy->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Attachments (Links)</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($invoice->attachments)
                            @foreach(array_filter(explode("\n", $invoice->attachments)) as $link)
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

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Invoice Lines</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">VAT %</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">VAT Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoice->lines as $line)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $line->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($line->quantity, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($line->unit_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($line->vat_percent, 2) }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($line->vat_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($line->line_total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $line->account->code ?? 'N/A' }} - {{ $line->account->name ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="5" class="px-6 py-4 text-right text-sm text-gray-900">Total:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($invoice->total, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

