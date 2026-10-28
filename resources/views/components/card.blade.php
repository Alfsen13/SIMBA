@props([
    'padding' => true,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden ' . $class . ($padding ? ' p-4' : '')]) }}>
    {{ $slot }}
</div>
