@extends('layouts.app')

@section('title', 'Create Asset Category')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Asset Category</h1>
        <p class="mt-1 text-sm text-gray-500">Add a new category for fixed assets</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('asset-categories.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Category Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="depreciation_rate" class="block text-sm font-medium text-gray-700">Depreciation Rate (%)</label>
                    <input type="number" name="depreciation_rate" id="depreciation_rate" step="0.01" min="0" max="100" value="{{ old('depreciation_rate') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('depreciation_rate') border-red-300 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Optional: Default depreciation rate for assets in this category</p>
                    @error('depreciation_rate')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('asset-categories.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

