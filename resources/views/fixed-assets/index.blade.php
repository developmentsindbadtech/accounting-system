@extends('layouts.app')

@section('title', 'Fixed Assets')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Fixed Assets</h1>
        <div class="flex items-center space-x-3">
            @if(auth()->user()->canEdit())
            <a href="{{ route('fixed-assets.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            <a href="{{ route('fixed-assets.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Add New Asset
            </a>
            @else
            <span class="bg-green-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50 text-sm" title="Viewer: No permission">
                Download CSV
            </span>
            <span class="bg-indigo-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50" title="Viewer: No permission">
                Add New Asset
            </span>
            @endif
        </div>
    </div>
    
    <!-- Sort Controls -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('fixed-assets.index') }}" class="flex items-center space-x-4">
            <label for="sort_by" class="text-sm font-medium text-gray-700">Sort by:</label>
            <select name="sort_by" id="sort_by" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="updated_at" {{ ($sortBy ?? 'updated_at') === 'updated_at' ? 'selected' : '' }}>Recently Updated</option>
                <option value="created_at" {{ ($sortBy ?? '') === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                <option value="asset_number" {{ ($sortBy ?? '') === 'asset_number' ? 'selected' : '' }}>Asset Number</option>
                <option value="name" {{ ($sortBy ?? '') === 'name' ? 'selected' : '' }}>Name</option>
                <option value="purchase_date" {{ ($sortBy ?? '') === 'purchase_date' ? 'selected' : '' }}>Purchase Date</option>
                <option value="purchase_cost" {{ ($sortBy ?? '') === 'purchase_cost' ? 'selected' : '' }}>Purchase Cost</option>
                <option value="net_book_value" {{ ($sortBy ?? '') === 'net_book_value' ? 'selected' : '' }}>Book Value</option>
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
                        <x-sortable-header field="asset_number" label="Asset #" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="fixed-assets.index" />
                        <x-sortable-header field="name" label="Name" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="fixed-assets.index" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <x-sortable-header field="purchase_date" label="Purchase Date" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="fixed-assets.index" />
                        <x-sortable-header field="purchase_cost" label="Purchase Cost" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="fixed-assets.index" />
                        <x-sortable-header field="net_book_value" label="Book Value" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="fixed-assets.index" />
                        <x-sortable-header field="status" label="Status" :currentSort="$sortBy ?? 'updated_at'" :currentDir="$sortDir ?? 'desc'" route="fixed-assets.index" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assets as $asset)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $asset->asset_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->purchase_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">SAR {{ number_format($asset->purchase_cost, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">SAR {{ number_format($asset->net_book_value, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($asset->status === 'active') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($asset->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('fixed-assets.show', $asset->id) }}" class="{{ $actionBtnBase }} border-indigo-200 text-indigo-600 hover:bg-indigo-50">View</a>
                                @if(auth()->user()->canEdit())
                                @if($asset->status === 'active')
                                <button onclick="showDisposeModal({{ $asset->id }})" class="{{ $actionBtnBase }} border-orange-200 text-orange-600 hover:bg-orange-50" title="Dispose Asset">Dispose</button>
                                @endif
                                <form action="{{ route('fixed-assets.destroy', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this fixed asset?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="{{ $actionBtnBase }} border-red-200 text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                                @else
                                @if($asset->status === 'active')
                                <span class="{{ $actionBtnBase }} border-orange-200 text-orange-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Dispose</span>
                                @endif
                                <span class="{{ $actionBtnBase }} border-red-200 text-red-400 bg-gray-100 cursor-not-allowed opacity-50" title="Viewer: No permission">Delete</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            No fixed assets found.@if(auth()->user()->canEdit()) <a href="{{ route('fixed-assets.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first asset</a>.@endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($assets->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <x-pagination-numeric :paginator="$assets" label="fixed assets" />
        </div>
        @endif
    </div>
</div>

<!-- Dispose Modal -->
<div id="disposeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dispose Fixed Asset</h3>
            <form id="disposeForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="disposal_date" class="block text-sm font-medium text-gray-700">Disposal Date *</label>
                    <input type="date" name="disposal_date" id="disposal_date" value="{{ date('Y-m-d') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDisposeModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="bg-orange-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-orange-700">
                        Dispose Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDisposeModal(assetId) {
    document.getElementById('disposeForm').action = '{{ route("fixed-assets.dispose", ":id") }}'.replace(':id', assetId);
    document.getElementById('disposeModal').classList.remove('hidden');
}

function closeDisposeModal() {
    document.getElementById('disposeModal').classList.add('hidden');
}
</script>
@endsection
