@props([
    'class' => '',
    'align' => 'left',
    'nowrap' => false,
    'muted' => false,
])

<td class="px-3 py-2 {{ $muted ? 'text-slate-500 dark:text-slate-400' : 'text-slate-700 dark:text-slate-300' }} text-{{ $align }} {{ $nowrap ? 'whitespace-nowrap' : '' }} {{ $class }}">
    {{ $slot }}
</td>
