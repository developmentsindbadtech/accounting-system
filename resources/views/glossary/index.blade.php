@extends('layouts.app')

@section('title', 'Glossary')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Glossary of Terms & Codes</h1>
            <p class="text-sm text-gray-600">Central reference for abbreviations, account codes, and IFRS definitions.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->canEdit())
            <a href="{{ route('glossary.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                Add Glossary Entry
            </a>
            @else
            <span class="bg-indigo-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50 text-sm" title="Viewer: No permission">
                Add Glossary Entry
            </span>
            @endif
        </div>
    </div>

    <div class="mb-4 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('glossary.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search term, code, description"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="module" class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                <select name="module" id="module" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="all" {{ $moduleFilter === 'all' ? 'selected' : '' }}>All Modules</option>
                    @foreach($modules as $value => $label)
                        <option value="{{ $value }}" {{ $moduleFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sort by</label>
                <select name="sort_by" id="sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="term" {{ $sortBy === 'term' ? 'selected' : '' }}>Term</option>
                    <option value="code" {{ $sortBy === 'code' ? 'selected' : '' }}>Code</option>
                    <option value="module" {{ $sortBy === 'module' ? 'selected' : '' }}>Module</option>
                    <option value="created_at" {{ $sortBy === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                    <option value="updated_at" {{ $sortBy === 'updated_at' ? 'selected' : '' }}>Recently Updated</option>
                </select>
            </div>
            <div class="flex items-center space-x-4">
                <div>
                    <label for="sort_dir" class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                    <select name="sort_dir" id="sort_dir" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="asc" {{ $sortDir === 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ $sortDir === 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                <div class="mt-6 flex items-center">
                    <input type="checkbox" name="ifrs_only" id="ifrs_only" value="1" {{ $ifrsOnly ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="ifrs_only" class="ml-2 block text-sm text-gray-700">IFRS Entries Only</label>
                </div>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Apply Filters</button>
            </div>
        </form>
    </div>

    <div id="glossary-list" class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <x-sortable-header field="term" label="Term / Abbreviation" :currentSort="$sortBy" :currentDir="$sortDir" route="glossary.index" :params="request()->query()" />
                        <x-sortable-header field="type" label="Type" :currentSort="$sortBy" :currentDir="$sortDir" route="glossary.index" :params="request()->query()" />
                        <x-sortable-header field="module" label="Module" :currentSort="$sortBy" :currentDir="$sortDir" route="glossary.index" :params="request()->query()" />
                        <x-sortable-header field="code" label="Code" :currentSort="$sortBy" :currentDir="$sortDir" route="glossary.index" :params="request()->query()" />
                        <x-sortable-header field="is_ifrs" label="IFRS" :currentSort="$sortBy" :currentDir="$sortDir" route="glossary.index" :params="request()->query()" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($glossaryItems as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->term }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->type ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $modules[$item->module] ?? ucfirst(str_replace('_', ' ', $item->module)) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">{{ $item->code ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($item->is_ifrs)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">IFRS</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">General</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <p class="whitespace-pre-line">{{ $item->description }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            @if(auth()->user()->canEdit())
                            <form action="{{ route('glossary.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this glossary entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1 text-xs font-semibold border border-red-200 text-red-600 rounded hover:bg-red-50">
                                    Delete
                                </button>
                            </form>
                            @else
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold border border-red-200 text-red-400 bg-gray-100 rounded cursor-not-allowed opacity-50" title="Viewer: No permission">
                                Delete
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No glossary entries yet.@if(auth()->user()->canEdit()) <a href="{{ route('glossary.create') }}" class="text-indigo-600 hover:text-indigo-900">Add the first entry</a>.@endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($glossaryItems->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <x-pagination-numeric :paginator="$glossaryItems" label="glossary entries" anchor="glossary-list" />
        </div>
        @endif
    </div>
</div>
@endsection

