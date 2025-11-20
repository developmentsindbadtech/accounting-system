@extends('layouts.app')

@section('title', 'Login')

@section('content')
<style>
    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    html {
        margin: 0;
        padding: 0;
        height: 100%;
    }
</style>
<div class="fixed inset-0 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background-image: url('https://sindbad.tech/assets-landing-light/images/kfad_tower.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; min-height: 100vh; width: 100vw;">
    <!-- Overlay for better readability -->
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    
    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Logo Section -->
        <div class="flex flex-col items-center space-y-6">
            <div class="bg-white rounded-2xl shadow-2xl p-8 flex items-center justify-center">
                <img src="{{ asset('stas.png') }}" alt="Sindbad.Tech Accounting System" class="h-24 w-auto">
            </div>
            
            <h2 class="text-center text-3xl font-extrabold text-white drop-shadow-lg">
                Sign in to your account
            </h2>
        </div>

        <!-- Microsoft Sign In Button -->
        <div class="bg-white rounded-xl shadow-2xl p-6">
            <a href="{{ route('login.azure') }}" class="w-full inline-flex justify-center items-center gap-3 px-6 py-3 border-2 border-gray-300 rounded-lg shadow-sm text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                <svg class="h-6 w-6" viewBox="0 0 23 23" aria-hidden="true">
                    <rect width="11" height="11" fill="#F35325"></rect>
                    <rect x="12" width="11" height="11" fill="#81BC06"></rect>
                    <rect y="12" width="11" height="11" fill="#05A6F0"></rect>
                    <rect x="12" y="12" width="11" height="11" fill="#FFBA08"></rect>
                </svg>
                Sign in with Microsoft
            </a>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
            <div class="text-red-600 text-sm text-center font-medium">
                {{ $errors->first('email') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

