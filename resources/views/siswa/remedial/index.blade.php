@php $title = __('siswa.remedial.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header
        :title="__('siswa.remedial.title')"
        :subtitle="__('siswa.remedial.subtitle', ['jumlah' => $remedials->total()])"
    />

    @if($remedials->isEmpty())
        <x-empty-state
            :message="__('siswa.remedial.belum_ada_tersedia')"
            icon='<svg class="w-6 h-6 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        />
        <p class="text-sm text-slate-500 dark:text-slate-400 text-center mt-1">
            {{ __('siswa.remedial.catatan_risiko') }}
        </p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($remedials as $r)
                <x-card padding="true" class="hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        @if($r->status === 'belum_dibaca')
                            <x-badge variant="baru" dot="true">{{ __('siswa.shared.status_baru') }}</x-badge>
                        @else
                            <x-badge variant="dibaca" dot="true">{{ __('siswa.shared.status_sudah_dibaca') }}</x-badge>
                        @endif
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $r->generated_at?->format('d M Y') ?: '-' }}
                        </span>
                    </div>

                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2 line-clamp-2">
                        {{ $r->topik_lemah ?: __('siswa.remedial.title') }}
                    </h3>

                    <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-3 mb-4">
                        {{ Str::limit(strip_tags($r->ringkasan), 120) }}
                    </p>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('siswa.remedial.provider', ['provider' => $r->provider_llm ?: '-']) }}
                        </span>
                        <a
                            href="{{ route('pelajar.remedial.show', $r) }}"
                            class="inline-flex items-center justify-center min-h-11 min-w-11 gap-1 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                        >
                            {{ __('siswa.remedial.baca') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </a>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
    {{ $remedials->links() }}
</div>
@endsection
