@php $title = $materi->judul; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-aktivitas-timer :log="$aktivitasLog" />
    <x-back-link
        href="{{ $materi->pertemuan_id ? route('pelajar.pertemuan.show', $materi->pertemuan_id) : route('pelajar.kelas.show', $materi->kelas_id) }}"
        :label="__('common.back')"
    />

    <h1 class="text-xl font-bold mt-2">{{ $materi->judul }}</h1>
    @if($materi->bab)
        <p class="text-slate-500 dark:text-slate-400">{{ __('siswa.shared.bab', ['bab' => $materi->bab]) }}</p>
    @endif
    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.guru', ['guru' => $materi->guru?->name]) }}</p>

    <x-ringkasan-materi :materi="$materi" />

    @if($materi->file_path)
        <div class="mt-6 flex items-center gap-3">
            <a
                href="{{ route('pelajar.materi.preview', $materi) }}"
                target="_blank"
                class="inline-flex items-center gap-2 bg-emerald-700 text-white px-4 py-2 rounded-lg hover:bg-emerald-800 transition-colors text-sm font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                {{ __('siswa.materi.lihat') }}
            </a>
            <a
                href="{{ route('pelajar.materi.download', $materi) }}"
                class="inline-flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                {{ __('common.download') }}
            </a>
        </div>

        @if(in_array(strtolower($materi->file_type ?? ''), ['pdf']))
            <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 75vh;">
                <iframe
                    src="{{ route('pelajar.materi.preview', $materi) }}"
                    class="w-full h-full border-0"
                ></iframe>
            </div>
        @elseif(in_array(strtolower($materi->file_type ?? ''), ['png', 'jpg', 'jpeg', 'gif', 'webp']))
            <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                <img
                    src="{{ route('pelajar.materi.preview', $materi) }}"
                    alt="{{ $materi->judul }}"
                    class="max-w-full mx-auto rounded-lg"
                />
            </div>
        @else
            <x-card class="mt-6">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.materi.filetype_prefix') }} <span class="font-mono">{{ strtoupper($materi->file_type ?? '?') }}</span> {{ __('siswa.materi.filetype_suffix') }}</p>
            </x-card>
        @endif
    @else
        <x-card class="mt-6">
            <p class="text-slate-500 dark:text-slate-400">{{ __('siswa.materi.tidak_ada_file') }}</p>
        </x-card>
    @endif

    @if($chunks->isNotEmpty())
        <div class="mt-4" id="kutipan-section" x-data="{ open: true }">
            <div class="flex items-center justify-between gap-3 mb-3">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200">
                    <svg class="w-5 h-5 inline text-emerald-700 dark:text-emerald-400 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    {{ __('siswa.materi.kutipan_ai_tutor') }}
                    <span class="ml-1 text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full align-middle">{{ $chunks->count() }}</span>
                </h2>
                <button
                    type="button"
                    @click="open = !open"
                    class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-controls="kutipan-list"
                >
                    <span x-show="open">{{ __('siswa.materi.sembunyikan_kutipan') }}</span>
                    <span x-show="!open">{{ __('siswa.materi.tampilkan_kutipan') }}</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
            <div id="kutipan-list" x-show="open" x-transition class="space-y-3">
                @foreach($chunks as $chunk)
                    @php
                        $isHighlighted = $highlight && stripos($chunk->content, $highlight) !== false;
                    @endphp
                    <div
                        id="chunk-{{ $chunk->id }}"
                        class="rounded-lg p-4 text-sm leading-relaxed transition-all duration-500
                            {{ $isHighlighted
                                ? 'bg-yellow-50 dark:bg-yellow-500/10 border-2 border-yellow-400 dark:border-yellow-700 shadow-md ring-2 ring-yellow-200 dark:ring-yellow-700/40'
                                : 'bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800'
                            }}"
                    >
                        <span class="text-xs font-semibold {{ $isHighlighted ? 'text-yellow-700 dark:text-yellow-300' : 'text-slate-500 dark:text-slate-400' }}">
                            {{ __('siswa.shared.bagian') }} {{ $chunk->chunk_index + 1 }}
                        </span>
                        <p class="mt-1 overflow-x-auto whitespace-pre-wrap break-words text-slate-700 dark:text-slate-300"><x-math :value="$chunk->content" /></p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@if($highlight)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('kutipan-section');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
@endpush
@endif
@endsection
