@props([
    'type' => 'button',
    'href' => null,
    'size' => 'md',
    'color' => 'gray',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center gap-1.5 font-medium rounded-lg transition-colors duration-150';
    $colors = [
        'gray' => 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700',
        'indigo' => 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-600/10 hover:bg-emerald-100 dark:hover:bg-emerald-600/20 border border-emerald-200 dark:border-emerald-800',
        'red' => 'text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 border border-red-200 dark:border-red-800',
    ];
    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-sm',
    ];
    $classes = $base . ' ' . ($colors[$color] ?? $colors['gray']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    if ($disabled) $classes .= ' opacity-50 cursor-not-allowed';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @if($disabled) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
