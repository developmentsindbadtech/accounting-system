@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div class="px-4 py-5 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Balance Sheet (IFRS Compliant)</h1>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->canEdit())
            <a href="{{ route('reports.balance-sheet.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                Download CSV
            </a>
            @else
            <span class="bg-green-300 text-white px-4 py-2 rounded-md cursor-not-allowed opacity-50 text-sm" title="Viewer: No permission">
                Download CSV
            </span>
            @endif
            <form method="GET" action="{{ route('reports.balance-sheet') }}" class="flex flex-wrap items-center gap-2">
                <label for="start_date" class="text-sm font-medium text-gray-700">From:</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? now()->startOfQuarter()->format('Y-m-d') }}" 
                    class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <label for="end_date" class="text-sm font-medium text-gray-700">To:</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? now()->endOfQuarter()->format('Y-m-d') }}" 
                    class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Update
                </button>
            </form>
            <a href="{{ route('reports.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Back to Reports
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-6 py-4">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Balance Sheet</h2>
                <p class="text-sm text-gray-600">
                    Period: {{ \Carbon\Carbon::parse($startDate ?? now()->startOfQuarter())->format('F d, Y') }} 
                    to {{ \Carbon\Carbon::parse($endDate ?? now()->endOfQuarter())->format('F d, Y') }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <!-- Assets Section -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">ASSETS</h3>
                    
                    <!-- Current Assets -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">Current Assets</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($currentAssets ?? [] as $asset)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $asset->code }} - {{ $asset->name }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($asset->balance ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No current assets</td>
                                </tr>
                                @endforelse
                                <tr class="border-t-2 border-gray-400">
                                    <td class="px-4 py-2 font-semibold text-gray-900">Total Current Assets</td>
                                    <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($totalCurrentAssets ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Non-Current Assets -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">Non-Current Assets</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($nonCurrentAssets ?? [] as $asset)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $asset->code }} - {{ $asset->name }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($asset->balance ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No non-current assets</td>
                                </tr>
                                @endforelse
                                <tr class="border-t-2 border-gray-400">
                                    <td class="px-4 py-2 font-semibold text-gray-900">Total Non-Current Assets</td>
                                    <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($totalNonCurrentAssets ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t-4 border-gray-800 mt-4 pt-2">
                        <table class="min-w-full">
                            <tbody>
                                <tr>
                                    <td class="px-4 py-2 text-lg font-bold text-gray-900">TOTAL ASSETS</td>
                                    <td class="px-4 py-2 text-lg font-bold font-mono text-right text-gray-900">SAR {{ number_format($totalAssets ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Liabilities & Equity Section -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">LIABILITIES & EQUITY</h3>
                    
                    <!-- Current Liabilities -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">Current Liabilities</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($currentLiabilities ?? [] as $liability)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $liability->code }} - {{ $liability->name }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($liability->balance ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No current liabilities</td>
                                </tr>
                                @endforelse
                                <tr class="border-t-2 border-gray-400">
                                    <td class="px-4 py-2 font-semibold text-gray-900">Total Current Liabilities</td>
                                    <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($totalCurrentLiabilities ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Non-Current Liabilities -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">Non-Current Liabilities</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($nonCurrentLiabilities ?? [] as $liability)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $liability->code }} - {{ $liability->name }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($liability->balance ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No non-current liabilities</td>
                                </tr>
                                @endforelse
                                <tr class="border-t-2 border-gray-400">
                                    <td class="px-4 py-2 font-semibold text-gray-900">Total Non-Current Liabilities</td>
                                    <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($totalNonCurrentLiabilities ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Equity -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">Equity</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($equityAccounts ?? [] as $equity)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $equity->code }} - {{ $equity->name }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($equity->balance ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-500">No equity accounts</td>
                                </tr>
                                @endforelse
                                @if(isset($retainedEarnings))
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">Retained Earnings</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right text-gray-900">SAR {{ number_format($retainedEarnings, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-t-2 border-gray-400">
                                    <td class="px-4 py-2 font-semibold text-gray-900">Total Equity</td>
                                    <td class="px-4 py-2 font-semibold font-mono text-right text-gray-900">SAR {{ number_format($totalEquityWithRetained ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t-4 border-gray-800 mt-4 pt-2">
                        <table class="min-w-full">
                            <tbody>
                                <tr>
                                    <td class="px-4 py-2 text-lg font-bold text-gray-900">TOTAL LIABILITIES & EQUITY</td>
                                    <td class="px-4 py-2 text-lg font-bold font-mono text-right text-gray-900">SAR {{ number_format(($totalLiabilities ?? 0) + ($totalEquityWithRetained ?? 0), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
