@props([
    'value' => '',
    'label' => '',
    'color' => 'emerald',
    'icon' => null,
])

@php
    $colors = [
        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-600/10', 'text' => 'text-emerald-700 dark:text-emerald-400'],
        'indigo' => ['bg' => 'bg-emerald-50 dark:bg-emerald-600/10', 'text' => 'text-emerald-700 dark:text-emerald-400'],
        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-500/10', 'text' => 'text-blue-600 dark:text-blue-400'],
        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-500/10', 'text' => 'text-purple-600 dark:text-purple-400'],
        'pink' => ['bg' => 'bg-pink-50 dark:bg-pink-500/10', 'text' => 'text-pink-600 dark:text-pink-400'],
        'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-500/10', 'text' => 'text-rose-600 dark:text-rose-400'],
        'cyan' => ['bg' => 'bg-cyan-50 dark:bg-cyan-500/10', 'text' => 'text-cyan-600 dark:text-cyan-400'],
        'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-500/10', 'text' => 'text-teal-600 dark:text-teal-400'],
        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-500/10', 'text' => 'text-orange-600 dark:text-orange-400'],
        'red' => ['bg' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-600 dark:text-red-400'],
        'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-500/10', 'text' => 'text-violet-600 dark:text-violet-400'],
        'green' => ['bg' => 'bg-green-50 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-400'],
        'slate' => ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-300'],
    ];
    $c = $colors[$color] ?? $colors['emerald'];
@endphp

<div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 p-3.5">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">{{ $label }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums leading-none text-slate-900 dark:text-white">{{ $value }}</p>
        </div>
        @if($icon)
            <div class="w-9 h-9 {{ $c['bg'] }} {{ $c['text'] }} rounded-lg flex items-center justify-center shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
