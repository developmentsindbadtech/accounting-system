@extends('layouts.app')

@section('title', 'View Journal Entry')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Journal Entry #{{ $journalEntry->entry_number }}</h1>
            <p class="mt-1 text-sm text-gray-500">Date: {{ $journalEntry->entry_date->format('M d, Y') }}</p>
        </div>
        <div class="flex space-x-3">
            @if($journalEntry->status === 'draft')
            <a href="{{ route('journal-entries.edit', $journalEntry) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Edit
            </a>
            @endif
            <a href="{{ route('journal-entries.index') }}" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Entry Details</h3>
        </div>
        <dl class="divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Entry Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $journalEntry->entry_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Entry Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $journalEntry->entry_date->format('M d, Y') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $journalEntry->description }}</dd>
                </div>
                @if($journalEntry->reference_number)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $journalEntry->reference_number }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($journalEntry->status === 'posted') bg-green-100 text-green-800
                            @elseif($journalEntry->status === 'reversed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($journalEntry->status) }}
                        </span>
                    </dd>
                </div>
                @if($journalEntry->posted_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Posted At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $journalEntry->posted_at->format('M d, Y H:i') }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Debit</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($journalEntry->total_debit, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Credit</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">SAR {{ number_format($journalEntry->total_credit, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $journalEntry->createdBy->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $journalEntry->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Attachments (Links)</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($journalEntry->attachments)
                            @foreach(array_filter(explode("\n", $journalEntry->attachments)) as $link)
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
            <h3 class="text-lg font-medium text-gray-900">Entry Lines</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($journalEntry->lines as $line)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="font-medium text-gray-900">{{ $line->account->code }}</div>
                            <div class="text-gray-500">{{ $line->account->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $line->description ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">
                            @if($line->debit > 0)
                                SAR {{ number_format($line->debit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">
                            @if($line->credit > 0)
                                SAR {{ number_format($line->credit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="2" class="px-6 py-4 text-right text-sm text-gray-900">Total:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($journalEntry->total_debit, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-gray-900">SAR {{ number_format($journalEntry->total_credit, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

