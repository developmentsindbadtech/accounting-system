@props([
    'paginator',
    'anchor' => null,
    'label' => 'records',
])

@if($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $perPage = $paginator->perPage();
        $total = $paginator->total();
        $firstItem = $paginator->firstItem() ?? (($current - 1) * $perPage + 1);
        $lastItem = $paginator->lastItem() ?? min($current * $perPage, $total);
        $pages = [];

        if ($last <= 7) {
            $pages = range(1, $last);
        } else {
            $pages[] = 1;
            $start = max(2, $current - 2);
            $end = min($last - 1, $current + 2);
            if ($start > 2) {
                $pages[] = '...';
            }
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
            if ($end < $last - 1) {
                $pages[] = '...';
            }
            $pages[] = $last;
        }

        $buildUrl = function ($page) use ($paginator, $anchor) {
            $url = $paginator->url($page);
            return $anchor ? "{$url}#{$anchor}" : $url;
        };
    @endphp

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-2 lg:space-y-0 text-sm text-gray-700">
        <div>
            Showing {{ $firstItem }} to {{ $lastItem }} of {{ $total }} {{ $label }}
        </div>
        <div class="flex items-center space-x-1">
            @foreach($pages as $pageNumber)
                @if($pageNumber === '...')
                    <span class="px-3 py-1 text-gray-400">...</span>
                @else
                    <a href="{{ $buildUrl($pageNumber) }}"
                       class="px-3 py-1 border rounded {{ $pageNumber === $current ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                        {{ $pageNumber }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif

