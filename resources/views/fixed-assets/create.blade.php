@extends('layouts.app')

@section('title', 'Create Fixed Asset')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Fixed Asset</h1>
        <p class="mt-1 text-sm text-gray-500">Add a new fixed asset to your system</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('fixed-assets.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="asset_number" class="block text-sm font-medium text-gray-700">Asset Number *</label>
                    <input type="text" name="asset_number" id="asset_number" value="{{ old('asset_number') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('asset_number') border-red-300 @enderror">
                    @error('asset_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Asset Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Asset Category</label>
                        <a href="{{ route('asset-categories.index') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-900">Manage Categories</a>
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
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date *</label>
                    <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('purchase_date') border-red-300 @enderror">
                    @error('purchase_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="purchase_cost" class="block text-sm font-medium text-gray-700">Purchase Cost (SAR) *</label>
                    <input type="number" name="purchase_cost" id="purchase_cost" step="0.01" min="0" value="{{ old('purchase_cost', 0) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('purchase_cost') border-red-300 @enderror">
                    @error('purchase_cost')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="depreciation_method" class="block text-sm font-medium text-gray-700">Depreciation Method *</label>
                    <select name="depreciation_method" id="depreciation_method" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('depreciation_method') border-red-300 @enderror">
                        <option value="">Select Method</option>
                        <option value="straight-line" {{ old('depreciation_method') == 'straight-line' ? 'selected' : '' }}>Straight-Line</option>
                        <option value="reducing-balance" {{ old('depreciation_method') == 'reducing-balance' ? 'selected' : '' }}>Reducing Balance</option>
                    </select>
                    @error('depreciation_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="useful_life_years" class="block text-sm font-medium text-gray-700">Useful Life (Years) *</label>
                    <input type="number" name="useful_life_years" id="useful_life_years" step="1" min="1" value="{{ old('useful_life_years', 1) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('useful_life_years') border-red-300 @enderror">
                    @error('useful_life_years')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="salvage_value" class="block text-sm font-medium text-gray-700">Salvage Value (SAR)</label>
                    <input type="number" name="salvage_value" id="salvage_value" step="0.01" min="0" value="{{ old('salvage_value', 0) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('salvage_value') border-red-300 @enderror">
                    @error('salvage_value')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="asset_account_id" class="block text-sm font-medium text-gray-700">Asset Account *</label>
                    <select name="asset_account_id" id="asset_account_id" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('asset_account_id') border-red-300 @enderror">
                        <option value="">Select Asset Account</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('asset_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} - {{ $account->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('asset_account_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="depreciation_expense_account_id" class="block text-sm font-medium text-gray-700">Depreciation Expense Account *</label>
                    <select name="depreciation_expense_account_id" id="depreciation_expense_account_id" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('depreciation_expense_account_id') border-red-300 @enderror">
                        <option value="">Select Expense Account</option>
                        @foreach($expenseAccounts as $account)
                        <option value="{{ $account->id }}" {{ old('depreciation_expense_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} - {{ $account->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('depreciation_expense_account_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="accumulated_depreciation_account_id" class="block text-sm font-medium text-gray-700">Accumulated Depreciation Account *</label>
                    <select name="accumulated_depreciation_account_id" id="accumulated_depreciation_account_id" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('accumulated_depreciation_account_id') border-red-300 @enderror">
                        <option value="">Select Asset Account</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('accumulated_depreciation_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} - {{ $account->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('accumulated_depreciation_account_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                <a href="{{ route('fixed-assets.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Asset
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
