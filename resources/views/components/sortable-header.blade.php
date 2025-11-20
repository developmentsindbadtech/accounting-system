@props(['field', 'label', 'currentSort', 'currentDir', 'route', 'params' => []])

@php
    $queryParams = array_merge($params, request()->query());
    $queryParams['sort_by'] = $field;
    $queryParams['sort_dir'] = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
    $url = route($route, $params) . '?' . http_build_query($queryParams);
@endphp

<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
    <a href="{{ $url }}" 
       class="hover:text-indigo-600 flex items-center space-x-1">
        <span>{{ $label }}</span>
        @if($currentSort === $field)
            <span>{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
        @endif
    </a>
</th>

