@php
    $locales = config('locales.supported', []);
    $currentLocale = app()->getLocale();
    $currentMeta = $locales[$currentLocale] ?? ['short' => strtoupper($currentLocale)];
@endphp

<div x-data="{ open: false }" class="relative">
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        aria-label="{{ __('navigation.choose_language') }}"
        aria-controls="locale-menu"
        :aria-expanded="open.toString()"
        class="flex min-h-11 min-w-11 items-center gap-1.5 p-2 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:focus-visible:outline-emerald-300"
    >
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>
        <span class="text-xs font-semibold">{{ $currentMeta['short'] ?? strtoupper($currentLocale) }}</span>
        <svg aria-hidden="true" class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        id="locale-menu"
        class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50"
    >
        @foreach($locales as $code => $meta)
            <form action="{{ route('locale.update') }}" method="POST">
                @csrf
                <input type="hidden" name="locale" value="{{ $code }}">
                <button
                    type="submit"
                    class="flex min-h-11 items-center justify-between w-full text-left px-4 py-2 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:focus-visible:outline-emerald-300
                        {{ $code === $currentLocale ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-600/10' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700' }}"
                >
                    <span>{{ $meta['label'] ?? strtoupper($code) }}</span>
                    @if($code === $currentLocale)
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
