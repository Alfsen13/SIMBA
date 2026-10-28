@props([
    'class' => '',
    'align' => 'left',
])

<th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap text-{{ $align }} {{ $class }}">
    {{ $slot }}
</th>
