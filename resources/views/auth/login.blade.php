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
        <div class="bg-white rounded-xl shadow-2xl p-6 space-y-6">
            <a href="{{ route('login.azure') }}" class="w-full inline-flex justify-center items-center gap-3 px-6 py-3 border-2 border-gray-300 rounded-lg shadow-sm text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                <svg class="h-6 w-6" viewBox="0 0 23 23" aria-hidden="true">
                    <rect width="11" height="11" fill="#F35325"></rect>
                    <rect x="12" width="11" height="11" fill="#81BC06"></rect>
                    <rect y="12" width="11" height="11" fill="#05A6F0"></rect>
                    <rect x="12" y="12" width="11" height="11" fill="#FFBA08"></rect>
                </svg>
                Sign in with Microsoft
            </a>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or sign in with email</span>
                </div>
            </div>

            <!-- Email/Password Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email address
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        value="{{ old('email') }}"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                        placeholder="Enter your email">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-base"
                        placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Sign in
                    </button>
                </div>
            </form>
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

