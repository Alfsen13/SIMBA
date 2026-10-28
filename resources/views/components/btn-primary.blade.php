@props([
    'type' => 'submit',
    'href' => null,
    'size' => 'md',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center gap-1.5 font-medium rounded-lg transition-colors duration-150';
    $gradient = 'text-white bg-emerald-700 hover:bg-emerald-800 shadow-sm';
    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-sm font-semibold',
    ];
    $classes = $base . ' ' . $gradient . ' ' . ($sizes[$size] ?? $sizes['md']);
    if ($disabled) $classes .= ' opacity-50 cursor-not-allowed';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @if($disabled) disabled @endif {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
