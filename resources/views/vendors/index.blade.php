@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Vendors</h1>
        <div class="flex items-center space-x-3">
            <a href="{{ route('vendors.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            @if(auth()->user()->canEdit())
            <a href="{{ route('vendors.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Add New Vendor
            </a>
            @else
            <span class="bg-indigo-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50" title="Viewer: No permission">
                Add New Vendor
            </span>
            @endif
        </div>
    </div>
    
    <!-- Sort Controls -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('vendors.index') }}" class="flex items-center space-x-4">
            <label for="sort_by" class="text-sm font-medium text-gray-700">Sort by:</label>
            <select name="sort_by" id="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="updated_at" {{ ($sortBy ?? 'updated_at') === 'updated_at' ? 'selected' : '' }}>Recently Updated</option>
                <option value="created_at" {{ ($sortBy ?? '') === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                <option value="name" {{ ($sortBy ?? '') === 'name' ? 'selected' : '' }}>Name</option>
                <option value="code" {{ ($sortBy ?? '') === 'code' ? 'selected' : '' }}>Code</option>
                <option value="balance" {{ ($sortBy ?? '') === 'balance' ? 'selected' : '' }}>Balance</option>
                <option value="is_active" {{ ($sortBy ?? '') === 'is_active' ? 'selected' : '' }}>Status</option>
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
                        <x-sortable-header field="code" label="Code" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="vendors.index" />
                        <x-sortable-header field="name" label="Name" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="vendors.index" />
                        <x-sortable-header field="email" label="Email" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="vendors.index" />
                        <x-sortable-header field="phone" label="Phone" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="vendors.index" />
                        <x-sortable-header field="balance" label="Balance" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="vendors.index" />
                        <x-sortable-header field="is_active" label="Status" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="vendors.index" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($vendors as $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $vendor->code ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $vendor->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $vendor->email ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $vendor->phone ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono {{ $vendor->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            SAR {{ number_format($vendor->balance, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($vendor->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('vendors.show', $vendor->id) }}" class="{{ $actionBtnBase }} border-indigo-200 text-indigo-600 hover:bg-indigo-50">View</a>
                                @if(auth()->user()->canEdit())
                                <form action="{{ route('vendors.toggle-active', $vendor->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $actionBtnBase }} {{ $vendor->is_active ? 'border-orange-200 text-orange-600 hover:bg-orange-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}" 
                                        title="{{ $vendor->is_active ? 'Deactivate' : 'Activate' }}">
                                        {{ $vendor->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this vendor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="{{ $actionBtnBase }} border-red-200 text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                                @else
                                <span class="{{ $actionBtnBase }} border-orange-200 text-orange-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">{{ $vendor->is_active ? 'Deactivate' : 'Activate' }}</span>
                                <span class="{{ $actionBtnBase }} border-red-200 text-red-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Delete</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No vendors found.@if(auth()->user()->canEdit()) <a href="{{ route('vendors.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first vendor</a>.@endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($vendors->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <x-pagination-numeric :paginator="$vendors" label="vendors" />
        </div>
        @endif
    </div>
</div>
@endsection
