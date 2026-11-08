@props(['active' => false, 'href' => '#', 'label' => '', 'icon' => ''])

@php
    $label = $label === 'Pengaturan AI' ? __('navigation.ai_settings') : $label;
@endphp

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => 'flex min-h-11 items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white ' . ($active ? 'bg-white/15 text-white shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10')]) }}>
    <svg aria-hidden="true" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    {{ $label }}
</a>