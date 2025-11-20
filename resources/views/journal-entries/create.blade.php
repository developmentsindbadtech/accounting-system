@extends('layouts.app')

@section('title', 'Create Journal Entry')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Journal Entry</h1>
        <p class="mt-1 text-sm text-gray-500">Record a manual journal entry</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form action="{{ route('journal-entries.store') }}" method="POST" class="p-6" id="journalEntryForm">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mb-6">
                <div>
                    <label for="entry_date" class="block text-sm font-medium text-gray-700">Entry Date *</label>
                    <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('entry_date') border-red-300 @enderror">
                    @error('entry_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                    <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('reference_number') border-red-300 @enderror">
                    @error('reference_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea name="description" id="description" rows="2" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Entry Lines *</h3>
                    <button type="button" onclick="addLine()" class="bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700">
                        Add Line
                    </button>
                </div>

                <div id="lines-container">
                    <div class="grid grid-cols-12 gap-4 mb-4 items-end line-row">
                        <div class="col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Account *</label>
                            <select name="lines[0][account_id]" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="lines[0][description]"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Debit</label>
                            <input type="number" name="lines[0][debit]" step="0.01" min="0" value="0"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm debit-input">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Credit</label>
                            <input type="number" name="lines[0][credit]" step="0.01" min="0" value="0"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm credit-input">
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Total Debit: </span>
                            <span id="total-debit" class="text-sm font-bold text-gray-900">SAR 0.00</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-700">Total Credit: </span>
                            <span id="total-credit" class="text-sm font-bold text-gray-900">SAR 0.00</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-sm font-medium text-gray-700">Difference: </span>
                        <span id="difference" class="text-sm font-bold"></span>
                    </div>
                </div>

                @error('lines')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('database')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('error')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
                <a href="{{ route('journal-entries.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Entry
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
            <div class="col-span-4">
                <label class="block text-sm font-medium text-gray-700">Account *</label>
                <select name="lines[${lineIndex}][account_id]" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Account</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-4">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" name="lines[${lineIndex}][description]"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Debit</label>
                <input type="number" name="lines[${lineIndex}][debit]" step="0.01" min="0" value="0"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm debit-input">
            </div>
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700">Credit</label>
                <input type="number" name="lines[${lineIndex}][credit]" step="0.01" min="0" value="0"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm credit-input">
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
    let totalDebit = 0;
    let totalCredit = 0;

    document.querySelectorAll('.debit-input').forEach(input => {
        totalDebit += parseFloat(input.value) || 0;
    });

    document.querySelectorAll('.credit-input').forEach(input => {
        totalCredit += parseFloat(input.value) || 0;
    });

    document.getElementById('total-debit').textContent = 'SAR ' + totalDebit.toFixed(2);
    document.getElementById('total-credit').textContent = 'SAR ' + totalCredit.toFixed(2);

    const difference = totalDebit - totalCredit;
    const diffElement = document.getElementById('difference');
    if (Math.abs(difference) < 0.01) {
        diffElement.textContent = 'SAR 0.00 (Balanced)';
        diffElement.className = 'text-sm font-bold text-green-600';
    } else {
        diffElement.textContent = 'SAR ' + difference.toFixed(2);
        diffElement.className = 'text-sm font-bold text-red-600';
    }
}

function attachEventListeners() {
    document.querySelectorAll('.debit-input, .credit-input').forEach(input => {
        input.removeEventListener('input', calculateTotals);
        input.addEventListener('input', calculateTotals);
    });
}

attachEventListeners();
updateDeleteButtons();

// Prevent form submission if not balanced
document.getElementById('journalEntryForm').addEventListener('submit', function(e) {
    const totalDebit = Array.from(document.querySelectorAll('.debit-input')).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
    const totalCredit = Array.from(document.querySelectorAll('.credit-input')).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
    
    if (Math.abs(totalDebit - totalCredit) > 0.01) {
        e.preventDefault();
        alert('Total debits must equal total credits. Current difference: SAR ' + Math.abs(totalDebit - totalCredit).toFixed(2));
    }
});
</script>
@endsection

