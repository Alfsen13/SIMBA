@props([
    'variant' => 'default',
    'dot' => false,
    'label' => null,
])

@php
    $palettes = [
        'green' => 'bg-green-50 text-green-700 ring-1 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30',
        'yellow' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30',
        'red' => 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30',
        'blue' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30',
        'purple' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200 dark:bg-purple-500/10 dark:text-purple-300 dark:ring-purple-500/30',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-600/10 dark:text-emerald-300 dark:ring-emerald-500/30',
        'gray' => 'bg-slate-50 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    ];

    $variantColor = [
        'verified' => 'green', 'pending' => 'yellow', 'rejected' => 'red',
        'selesai' => 'blue', 'baru' => 'yellow', 'dibaca' => 'green',
        'operator' => 'purple', 'pengajar' => 'blue', 'pelajar' => 'emerald',
        'success' => 'green', 'warning' => 'yellow', 'danger' => 'red',
        'info' => 'blue', 'default' => 'gray',
    ];

    $variants = [];
    foreach ($variantColor as $key => $color) {
        $variants[$key] = $palettes[$color];
    }

    $dotColors = [
        'verified' => 'bg-green-500', 'pending' => 'bg-yellow-500', 'rejected' => 'bg-red-500',
        'selesai' => 'bg-blue-500', 'baru' => 'bg-yellow-500', 'dibaca' => 'bg-green-500',
        'operator' => 'bg-purple-500', 'pengajar' => 'bg-blue-500', 'pelajar' => 'bg-emerald-600',
        'success' => 'bg-green-500', 'warning' => 'bg-yellow-500', 'danger' => 'bg-red-500',
        'info' => 'bg-blue-500', 'default' => 'bg-slate-500',
    ];
@endphp

<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium {{ $variants[$variant] ?? $variants['default'] }}">
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$variant] ?? $dotColors['default'] }}"></span>
    @endif
    {{ $label ?? $slot }}
</span>
