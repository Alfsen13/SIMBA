@props(['items' => []])

{{--
    Daftar tanya-jawab dari hasil `MateriRemedial::latihan_items`. Jawaban
    disembunyikan secara default dan bisa dibuka per soal (Alpine).
--}}

@if(! empty($items))
    <ol class="space-y-3">
        @foreach($items as $i => $item)
            <li class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-3">
                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                    <span class="text-slate-500 dark:text-slate-400">{{ $i + 1 }}.</span>
                    <x-math :value="$item['soal'] ?? ''" />
                </p>

                @if(! empty($item['jawaban']))
                    <div class="mt-2" x-data="{ buka: false }">
                        <button
                            type="button"
                            @click="buka = !buka"
                            :aria-expanded="buka ? 'true' : 'false'"
                            class="inline-flex items-center min-h-11 min-w-11 gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span x-show="!buka">{{ __('siswa.remedial.lihat_jawaban') }}</span>
                            <span x-show="buka" x-cloak>{{ __('siswa.remedial.sembunyikan_jawaban') }}</span>
                        </button>

                        <div
                            x-show="buka"
                            x-cloak
                            x-transition
                            class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-700"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ __('siswa.remedial.jawaban') }}
                            </p>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">
                                <x-math :value="$item['jawaban']" />
                            </p>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
@endif
