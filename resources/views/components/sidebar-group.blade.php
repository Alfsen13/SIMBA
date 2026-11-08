@props([
    'label' => '',
    'icon' => '',
    'open' => false,
])

<div x-data="{ open: @json($open) }" class="space-y-0.5">
    @php($panelId = 'sidebar-group-'.\Illuminate\Support\Str::slug($label))
    <button type="button" @click="open = ! open"
        aria-controls="{{ $panelId }}"
        :aria-expanded="open.toString()"
        class="flex min-h-11 items-center justify-between w-full px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        :class="open ? 'bg-white/15 text-white shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10'"
    >
        <span class="flex items-center gap-3">
            <svg aria-hidden="true" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
            {{ $label }}
        </span>
        <svg aria-hidden="true" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div id="{{ $panelId }}" x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="ml-3 border-l border-white/10 pl-2 space-y-0.5">
        {{ $slot }}
    </div>
</div>
