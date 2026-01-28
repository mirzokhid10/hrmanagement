@props(['route', 'icon', 'label', 'activePattern' => null])

@php
    // If no specific pattern provided, check if the current route starts with the route name
    // e.g., 'admin.employees' matches 'admin.employees.index' and 'admin.employees.show'
    $isActive = $activePattern
        ? request()->routeIs($activePattern)
        : Route::has($route) && request()->routeIs($route . '*');

    $href = Route::has($route) ? route($route) : '#';
@endphp

<li class="menu-item {{ $isActive ? 'active' : '' }}">
    <a class="menu-link {{ $isActive ? 'active' : '' }}" href="{{ $href }}">
        <i class="{{ $icon }}"></i>
        <span class="menu-label">{{ $label }}</span>
    </a>
</li>
