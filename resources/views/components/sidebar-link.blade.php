@props(['active' => false, 'href' => '#', 'label' => '', 'icon' => ''])

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 ' . ($active ? 'bg-white/15 text-white shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10')]) }}>
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    {{ $label }}
</a>