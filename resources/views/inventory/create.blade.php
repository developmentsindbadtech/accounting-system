@extends('layouts.app')

@section('title', 'Create Inventory Item')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Inventory Item</h1>
        <p class="mt-1 text-sm text-gray-500">Add a new item to your inventory</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('inventory.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code/SKU *</label>
                    <input type="text" name="item_code" id="item_code" value="{{ old('item_code') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('item_code') border-red-300 @enderror">
                    @error('item_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Item Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Item Type *</label>
                    <select name="type" id="type" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('type') border-red-300 @enderror">
                        <option value="">Select Type</option>
                        <option value="product" {{ old('type') == 'product' ? 'selected' : '' }}>Product</option>
                        <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service</option>
                    </select>
                    @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <a href="{{ route('item-categories.index') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-900">Manage Categories</a>
                    </div>
                    <select name="category_id" id="category_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('category_id') border-red-300 @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit_of_measure" class="block text-sm font-medium text-gray-700">Unit of Measure</label>
                    <select name="unit_of_measure" id="unit_of_measure"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="Unit" {{ old('unit_of_measure') == 'Unit' ? 'selected' : '' }}>Unit</option>
                        <option value="Piece" {{ old('unit_of_measure') == 'Piece' ? 'selected' : '' }}>Piece</option>
                        <option value="Box" {{ old('unit_of_measure') == 'Box' ? 'selected' : '' }}>Box</option>
                        <option value="Kg" {{ old('unit_of_measure') == 'Kg' ? 'selected' : '' }}>Kg</option>
                        <option value="Liter" {{ old('unit_of_measure') == 'Liter' ? 'selected' : '' }}>Liter</option>
                        <option value="Meter" {{ old('unit_of_measure') == 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                </div>

                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700">Cost Price (SAR)</label>
                    <input type="number" name="cost_price" id="cost_price" step="0.01" min="0" value="{{ old('cost_price', 0) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('cost_price') border-red-300 @enderror">
                    @error('cost_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity_on_hand" class="block text-sm font-medium text-gray-700">Quantity on Hand</label>
                    <input type="number" name="quantity_on_hand" id="quantity_on_hand" step="0.01" min="0" value="{{ old('quantity_on_hand', 0) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('quantity_on_hand') border-red-300 @enderror">
                    @error('quantity_on_hand')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Only applicable for products</p>
                </div>

                <div>
                    <label for="reorder_point" class="block text-sm font-medium text-gray-700">Reorder Point</label>
                    <input type="number" name="reorder_point" id="reorder_point" step="0.01" min="0" value="{{ old('reorder_point', 0) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('reorder_point') border-red-300 @enderror">
                    @error('reorder_point')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Only applicable for products</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="attachments" class="block text-sm font-medium text-gray-700">Attachments (Links)</label>
                    <textarea name="attachments" id="attachments" rows="3" placeholder="Paste attachment links here (one per line)"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('attachments') border-red-300 @enderror">{{ old('attachments') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Optional: Paste links to attachments (one per line)</p>
                    @error('attachments')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('inventory.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
