@extends('layouts.app')

@section('title', 'View Account')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $account->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Account Code: {{ $account->code }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('chart-of-accounts.edit', $account->id) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Edit
            </a>
            <a href="{{ route('chart-of-accounts.index') }}" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Account Details</h3>
        </div>
        <dl class="divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Account Code</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $account->code }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Account Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $account->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($account->type === 'Asset') bg-blue-100 text-blue-800
                            @elseif($account->type === 'Liability') bg-red-100 text-red-800
                            @elseif($account->type === 'Equity') bg-green-100 text-green-800
                            @elseif($account->type === 'Revenue') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $account->type }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Level</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $account->level }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Parent Account</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($account->parent_id && $account->parent)
                            <a href="{{ route('chart-of-accounts.show', $account->parent->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $account->parent->code }} - {{ $account->parent->name }}
                            </a>
                        @else
                            <span class="text-gray-400">None</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Current Balance</dt>
                    <dd class="mt-1 text-sm font-mono">
                        <span class="{{ $account->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            SAR {{ number_format($account->balance, 2) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        @if($account->is_active)
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
                    <dd class="mt-1 text-sm text-gray-900">{{ $account->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Attachments (Links)</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($account->attachments)
                            @foreach(array_filter(explode("\n", $account->attachments)) as $link)
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

        @if($account->children()->count() > 0)
        <div class="px-6 py-5 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sub-Accounts</h3>
            <ul class="divide-y divide-gray-200">
                @foreach($account->children as $child)
                <li class="py-3">
                    <a href="{{ route('chart-of-accounts.show', $child->id) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $child->code }} - {{ $child->name }}
                    </a>
                    <span class="ml-2 text-sm text-gray-500">
                        (Balance: SAR {{ number_format($child->balance, 2) }})
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection

