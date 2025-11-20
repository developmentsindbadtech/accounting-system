@extends('layouts.app')

@section('title', '403 - Unauthorized')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-gray-300">403</h1>
            <h2 class="text-3xl font-bold text-gray-900 mt-4">Unauthorized Action</h2>
            <p class="text-gray-600 mt-2">
                You don't have permission to perform this action.
            </p>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-red-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm text-red-800">
                    Your current role (<strong>{{ ucfirst(auth()->user()->role ?? 'Viewer') }}</strong>) does not have the required permissions for this action.
                </p>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ url()->previous() ?? route('dashboard') }}" class="block w-full bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 transition-colors">
                Return to Dashboard
            </a>
        </div>

        @if(auth()->check() && auth()->user()->isViewer())
        <div class="mt-6 text-sm text-gray-500">
            <p>As a <strong>Viewer</strong>, you can only view data. Contact your administrator if you need additional permissions.</p>
        </div>
        @endif
    </div>
</div>
@endsection

