@extends('layouts.app')

@section('title', 'Create Vendor')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Vendor</h1>
        <p class="mt-1 text-sm text-gray-500">Add a new vendor to your system</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('vendors.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Vendor Code</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('code') border-red-300 @enderror">
                    @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Vendor Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-300 @enderror">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('phone') border-red-300 @enderror">
                    @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="2"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('address') border-red-300 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_id" class="block text-sm font-medium text-gray-700">Tax ID (VAT Registration)</label>
                    <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('tax_id') border-red-300 @enderror">
                    @error('tax_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_terms" class="block text-sm font-medium text-gray-700">Payment Terms</label>
                    <select name="payment_terms" id="payment_terms"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('payment_terms') border-red-300 @enderror">
                        <option value="">Select Terms</option>
                        <option value="Net 15" {{ old('payment_terms') == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                        <option value="Net 30" {{ old('payment_terms') == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                        <option value="Net 45" {{ old('payment_terms') == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                        <option value="Net 60" {{ old('payment_terms') == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                        <option value="Due on Receipt" {{ old('payment_terms') == 'Due on Receipt' ? 'selected' : '' }}>Due on Receipt</option>
                    </select>
                    @error('payment_terms')
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
                <a href="{{ route('vendors.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Vendor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

