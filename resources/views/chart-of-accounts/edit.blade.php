@extends('layouts.app')

@section('title', 'Edit Account')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Account</h1>
        <p class="mt-1 text-sm text-gray-500">Update account information</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('chart-of-accounts.update', $account) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Account Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $account->code) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('code') border-red-300 @enderror">
                    @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Account Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $account->name) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Account Type *</label>
                    <select name="type" id="type" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('type') border-red-300 @enderror">
                        <option value="">Select a type</option>
                        <option value="Asset" {{ old('type', $account->type) === 'Asset' ? 'selected' : '' }}>Asset</option>
                        <option value="Liability" {{ old('type', $account->type) === 'Liability' ? 'selected' : '' }}>Liability</option>
                        <option value="Equity" {{ old('type', $account->type) === 'Equity' ? 'selected' : '' }}>Equity</option>
                        <option value="Revenue" {{ old('type', $account->type) === 'Revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="Expense" {{ old('type', $account->type) === 'Expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                    @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Account</label>
                    <select name="parent_id" id="parent_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('parent_id') border-red-300 @enderror">
                        <option value="">None (Top Level)</option>
                        @foreach($parentAccounts as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->code }} - {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('chart-of-accounts.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

