@extends('layouts.app')

@section('title', 'Create Bill')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Bill</h1>
        <p class="mt-1 text-sm text-gray-500">Record a vendor bill</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('bills.store') }}" method="POST" class="p-6" id="billForm">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mb-6">
                <div>
                    <label for="vendor_id" class="block text-sm font-medium text-gray-700">Vendor *</label>
                    <select name="vendor_id" id="vendor_id" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('vendor_id') border-red-300 @enderror">
                        <option value="">Select Vendor</option>
                        @foreach(\App\Models\Vendor::where('is_active', true)->get() as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('vendor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bill_date" class="block text-sm font-medium text-gray-700">Bill Date *</label>
                    <input type="date" name="bill_date" id="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('bill_date') border-red-300 @enderror">
                    @error('bill_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date *</label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('due_date') border-red-300 @enderror">
                    @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reference" class="block text-sm font-medium text-gray-700">Reference Number</label>
                    <input type="text" name="reference" id="reference" value="{{ old('reference') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('reference') border-red-300 @enderror">
                    @error('reference')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Bill Lines *</h3>
                    <button type="button" onclick="addLine()" class="bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700">
                        Add Line
                    </button>
                </div>

                <div id="lines-container">
                    <div class="grid grid-cols-12 gap-4 mb-4 items-end line-row">
                        <div class="col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Description *</label>
                            <input type="text" name="lines[0][description]" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="lines[0][quantity]" step="0.01" min="0" value="1"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm qty-input">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                            <input type="number" name="lines[0][unit_price]" step="0.01" min="0" value="0"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm price-input">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                            <input type="number" name="lines[0][tax_rate]" step="0.01" min="0" max="100" value="15"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm tax-input">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Line Total</label>
                            <input type="text" readonly
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm line-total">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                            <button type="button" onclick="removeLine(this)" class="mt-1 w-full bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700" disabled>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-gray-50 rounded-md">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                            <span id="subtotal" class="text-sm font-bold text-gray-900">SAR 0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">VAT (15%):</span>
                            <span id="vat-amount" class="text-sm font-bold text-gray-900">SAR 0.00</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-sm font-medium text-gray-900">Total:</span>
                            <span id="total" class="text-lg font-bold text-gray-900">SAR 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
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
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('bills.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Bill
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let lineIndex = 1;

function addLine() {
    const container = document.getElementById('lines-container');
    const lineHtml = `
        <div class="grid grid-cols-12 gap-4 mb-4 items-end line-row">
            <div class="col-span-3">
                <label class="block text-sm font-medium text-gray-700">Description *</label>
                <input type="text" name="lines[${lineIndex}][description]" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="lines[${lineIndex}][quantity]" step="0.01" min="0" value="1"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm qty-input">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                <input type="number" name="lines[${lineIndex}][unit_price]" step="0.01" min="0" value="0"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm price-input">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                <input type="number" name="lines[${lineIndex}][tax_rate]" step="0.01" min="0" max="100" value="15"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm tax-input">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Line Total</label>
                <input type="text" readonly
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm line-total">
            </div>
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <button type="button" onclick="removeLine(this)" class="mt-1 w-full bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', lineHtml);
    lineIndex++;
    attachEventListeners();
    updateDeleteButtons();
}

function removeLine(button) {
    const lineRow = button.closest('.line-row');
    lineRow.remove();
    calculateTotals();
    updateDeleteButtons();
}

function updateDeleteButtons() {
    const lineRows = document.querySelectorAll('.line-row');
    lineRows.forEach((row, index) => {
        const deleteButton = row.querySelector('button[onclick="removeLine(this)"]');
        if (deleteButton) {
            if (lineRows.length === 1) {
                deleteButton.disabled = true;
                deleteButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                deleteButton.disabled = false;
                deleteButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    });
}

function calculateTotals() {
    let subtotal = 0;
    let vatAmount = 0;

    document.querySelectorAll('.line-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;
        
        const lineSubtotal = qty * price;
        const lineVat = lineSubtotal * (taxRate / 100);
        const lineTotal = lineSubtotal + lineVat;

        row.querySelector('.line-total').value = 'SAR ' + lineTotal.toFixed(2);
        subtotal += lineSubtotal;
        vatAmount += lineVat;
    });

    document.getElementById('subtotal').textContent = 'SAR ' + subtotal.toFixed(2);
    document.getElementById('vat-amount').textContent = 'SAR ' + vatAmount.toFixed(2);
    document.getElementById('total').textContent = 'SAR ' + (subtotal + vatAmount).toFixed(2);
}

function attachEventListeners() {
    document.querySelectorAll('.qty-input, .price-input, .tax-input').forEach(input => {
        input.removeEventListener('input', calculateTotals);
        input.addEventListener('input', calculateTotals);
    });
}

attachEventListeners();
calculateTotals();
updateDeleteButtons();
</script>
@endsection

