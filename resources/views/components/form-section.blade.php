@props([
    'title',
    'hint' => null,
])

{{-- Heading ringkas untuk satu blok field dalam form panjang (mis. form register):
     judul + petunjuk singkat dengan gaya label yang sama seperti form-input. --}}
<div {{ $attributes->merge(['class' => 'pt-1']) }}>
    <div class="mb-3">
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $title }}</p>
        @if ($hint)
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $hint }}</p>
        @endif
    </div>

    {{ $slot }}
</div>
