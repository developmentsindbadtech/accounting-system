<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Sindbad.Tech Accounting System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50">
    @auth
    <nav class="bg-white shadow-sm border-b border-gray-200" x-data="{ mobileMenuOpen: false }">
        <div class="w-full">
            <div class="flex justify-between items-center h-16">
                <!-- Logo - Left -->
                <div class="flex-shrink-0 flex items-center pl-4 sm:pl-6 lg:pl-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('stas.png') }}" alt="Sindbad.Tech Accounting System" class="h-10 sm:h-12 lg:h-14 w-auto object-contain">
                    </a>
                </div>
                
                <!-- Desktop Navigation - Center -->
                <div class="hidden lg:flex lg:items-center lg:gap-1 xl:gap-2 flex-1 justify-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('dashboard') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Dashboard</a>
                    <a href="{{ route('chart-of-accounts.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('chart-of-accounts.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Chart of Accounts</a>
                    <a href="{{ route('journal-entries.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('journal-entries.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Journal Entries</a>
                    <a href="{{ route('customers.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('customers.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Customers</a>
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('invoices.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Invoices</a>
                    <a href="{{ route('vendors.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('vendors.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Vendors</a>
                    <a href="{{ route('bills.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('bills.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Bills</a>
                    <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('inventory.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Inventory</a>
                    <a href="{{ route('fixed-assets.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('fixed-assets.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Fixed Assets</a>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('reports.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Reports</a>
                    <a href="{{ route('glossary.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('glossary.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Glossary</a>
                    <a href="{{ route('logs.index') }}" class="inline-flex items-center px-3 xl:px-4 py-2 rounded-t-lg text-xs xl:text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('logs.*') ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Logs</a>
                    </div>
                
                <!-- Right Side - User Info & Mobile Menu -->
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden ml-4 mr-4 sm:mr-6 lg:mr-8 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg x-show="!mobileMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    
                    <!-- User Info - Desktop -->
                    <div class="hidden sm:flex items-center ml-4 pr-4 sm:pr-6 lg:pr-8 gap-3">
                        <img src="{{ auth()->user()->getProfilePictureUrl() }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full object-cover border-2 border-gray-300" onerror="this.src='{{ auth()->user()->getInitialsAvatar() }}'">
                        <span class="text-sm text-gray-700 truncate max-w-[150px]">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 whitespace-nowrap">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-100 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="lg:hidden border-t border-gray-200">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Dashboard</a>
                <a href="{{ route('chart-of-accounts.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('chart-of-accounts.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Chart of Accounts</a>
                <a href="{{ route('journal-entries.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('journal-entries.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Journal Entries</a>
                <a href="{{ route('customers.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Customers</a>
                <a href="{{ route('invoices.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('invoices.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Invoices</a>
                <a href="{{ route('vendors.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('vendors.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Vendors</a>
                <a href="{{ route('bills.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('bills.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Bills</a>
                <a href="{{ route('inventory.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('inventory.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Inventory</a>
                <a href="{{ route('fixed-assets.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('fixed-assets.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Fixed Assets</a>
                <a href="{{ route('reports.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Reports</a>
                <a href="{{ route('glossary.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('glossary.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Glossary</a>
                <a href="{{ route('logs.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors {{ request()->routeIs('logs.*') ? 'bg-blue-50 text-blue-600 border-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300' }}">Logs</a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <img src="{{ auth()->user()->getProfilePictureUrl() }}" alt="{{ auth()->user()->name }}" class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" onerror="this.src='{{ auth()->user()->getInitialsAvatar() }}'">
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4 sm:mx-6 lg:mx-8" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 sm:mx-6 lg:mx-8" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 sm:mx-6 lg:mx-8" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>

    @livewireScripts
</body>
</html>

