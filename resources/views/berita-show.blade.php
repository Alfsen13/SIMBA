@extends('layouts.guest')

@section('title', $beritum->judul . ' - SIMBA')

@section('container_width', 'max-w-3xl')

@section('content')
    <div class="text-left w-full">
        <x-back-link
            href="{{ route('home') }}"
            :label="__('publik.kembali_ke_beranda')"
        />

        <article>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2">
                {{ $beritum->judul }}
            </h1>

            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mb-4">
                <span>{{ $beritum->published_at?->format('d F Y') }}</span>
                <span>&middot;</span>
                <span>{{ $beritum->user?->name ?? 'Admin' }}</span>
            </div>

            @if($beritum->excerpt)
                <p class="text-base text-slate-600 dark:text-slate-400 mb-4 font-medium">
                    {{ $beritum->excerpt }}
                </p>
            @endif

            <div class="prose prose-slate dark:prose-invert max-w-none text-sm leading-relaxed">
                {!! nl2br(e($beritum->konten)) !!}
            </div>
        </article>

        @if($beritaLainnya->isNotEmpty())
            <hr class="my-8 border-slate-200 dark:border-slate-800">

            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">
                {{ __('publik.berita.lainnya') }}
            </h2>

            <div class="space-y-3">
                @foreach($beritaLainnya as $item)
                    <a
                        href="{{ route('berita.show', $item) }}"
                        class="block p-3.5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors"
                    >
                        <h3 class="font-medium text-slate-900 dark:text-slate-100">
                            {{ $item->judul }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $item->published_at?->format('d F Y') }}
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
