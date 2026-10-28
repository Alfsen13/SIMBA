@props([
    'class' => '',
    'clickable' => false,
])

<tr class="hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition-colors {{ $clickable ? 'cursor-pointer' : '' }} {{ $class }}">
    {{ $slot }}
</tr>
