@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
        <div class="flex items-center space-x-3">
            <a href="{{ route('chart-of-accounts.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            @if(auth()->user()->canEdit())
            <a href="{{ route('chart-of-accounts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Add New Account
            </a>
            @else
            <span class="bg-indigo-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50" title="Viewer: No permission">
                Add New Account
            </span>
            @endif
        </div>
    </div>
    
    <!-- Sort Controls -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('chart-of-accounts.index') }}" class="flex items-center space-x-4">
            <label for="sort_by" class="text-sm font-medium text-gray-700">Sort by:</label>
            <select name="sort_by" id="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="updated_at" {{ ($sortBy ?? 'updated_at') === 'updated_at' ? 'selected' : '' }}>Recently Updated</option>
                <option value="created_at" {{ ($sortBy ?? '') === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                <option value="code" {{ ($sortBy ?? '') === 'code' ? 'selected' : '' }}>Code</option>
                <option value="name" {{ ($sortBy ?? '') === 'name' ? 'selected' : '' }}>Name</option>
                <option value="type" {{ ($sortBy ?? '') === 'type' ? 'selected' : '' }}>Type</option>
                <option value="level" {{ ($sortBy ?? '') === 'level' ? 'selected' : '' }}>Level</option>
                <option value="balance" {{ ($sortBy ?? '') === 'balance' ? 'selected' : '' }}>Balance</option>
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
                        <x-sortable-header field="code" label="Code" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="chart-of-accounts.index" />
                        <x-sortable-header field="name" label="Name" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="chart-of-accounts.index" />
                        <x-sortable-header field="type" label="Type" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="chart-of-accounts.index" />
                        <x-sortable-header field="level" label="Level" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="chart-of-accounts.index" />
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('chart-of-accounts.index', array_merge(request()->query(), ['sort_by' => 'balance', 'sort_dir' => ($sortBy ?? '') === 'balance' && ($sortDir ?? '') === 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-indigo-600 flex items-center justify-end space-x-1">
                                <span>Balance</span>
                                @if(($sortBy ?? '') === 'balance')
                                    <span>{{ ($sortDir ?? '') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $account->code }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <span class="text-gray-900">{{ str_repeat('&nbsp;&nbsp;&nbsp;', $account->level - 1) }}{{ $account->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($account->type === 'Asset') bg-blue-100 text-blue-800
                                @elseif($account->type === 'Liability') bg-red-100 text-red-800
                                @elseif($account->type === 'Equity') bg-green-100 text-green-800
                                @elseif($account->type === 'Revenue') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $account->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $account->level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-sm">
                            <span class="{{ $account->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                SAR {{ number_format($account->balance, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex justify-center items-center space-x-2">
                                <a href="{{ route('chart-of-accounts.show', $account->id) }}" class="{{ $actionBtnBase }} border-indigo-200 text-indigo-600 hover:bg-indigo-50">View</a>
                                @if(auth()->user()->canEdit())
                                <a href="{{ route('chart-of-accounts.edit', $account->id) }}" class="{{ $actionBtnBase }} border-blue-200 text-blue-600 hover:bg-blue-50">Edit</a>
                                <form action="{{ route('chart-of-accounts.toggle-active', $account->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $actionBtnBase }} {{ $account->is_active ? 'border-orange-200 text-orange-600 hover:bg-orange-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}" 
                                        title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                                        {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('chart-of-accounts.destroy', $account->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="{{ $actionBtnBase }} border-red-200 text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                                @else
                                <span class="{{ $actionBtnBase }} border-blue-200 text-blue-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Edit</span>
                                <span class="{{ $actionBtnBase }} border-orange-200 text-orange-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">{{ $account->is_active ? 'Deactivate' : 'Activate' }}</span>
                                <span class="{{ $actionBtnBase }} border-red-200 text-red-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Delete</span>
                                @endif
                            </div>
                            @if(!$account->is_active)
                            <div class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No accounts found.@if(auth()->user()->canEdit()) <a href="{{ route('chart-of-accounts.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first account</a>.@endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($accounts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <x-pagination-numeric :paginator="$accounts" label="accounts" />
        </div>
        @endif
    </div>
</div>
@endsection

