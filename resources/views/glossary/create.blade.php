@extends('layouts.app')

@section('title', 'Add Glossary Entry')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Add Glossary Entry</h1>
        <p class="text-sm text-gray-600">Capture terminology, abbreviations, and account codes for quick reference.</p>
    </div>

    <div class="bg-white shadow sm:rounded-md">
        <form method="POST" action="{{ route('glossary.store') }}" class="p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="term" class="block text-sm font-medium text-gray-700">Term / Abbreviation *</label>
                    <input type="text" name="term" id="term" value="{{ old('term') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type (e.g., Code, KPI, Acronym)</label>
                    <input type="text" name="type" id="type" value="{{ old('type') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="module" class="block text-sm font-medium text-gray-700">Module *</label>
                    <select name="module" id="module" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="" disabled {{ old('module') ? '' : 'selected' }}>Select module</option>
                        @foreach($modules as $value => $label)
                            <option value="{{ $value }}" {{ old('module') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Code (optional)</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="e.g., 2000 or INV-001">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                <textarea name="description" id="description" rows="5" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="Provide context, IFRS guidance, or how the term is used across modules">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_ifrs" id="is_ifrs" value="1" {{ old('is_ifrs') ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="is_ifrs" class="ml-2 block text-sm text-gray-700">Mark as IFRS-specific definition</label>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('glossary.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Save Entry</button>
            </div>
        </form>
    </div>
</div>
@endsection

