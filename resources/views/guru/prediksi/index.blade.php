@extends('layouts.app')

@section('title', __('guru_analitik.prediksi.page_title') . ' - SIMBA')

@section('content')
    <x-page-header :title="__('guru_analitik.prediksi.risk_page_title')">
        <x-slot:actions>
            {{-- Satu siklus prediksi hanya 0,8-5,7 detik, jadi dijalankan sinkron
                 di request ini: guru langsung melihat hasilnya, tidak perlu reload,
                 dan tidak bisa menekan tombol berkali-kali sementara pekerjaan yang
                 sama masih jalan di belakang. Pembuatan materi remedial TIDAK ikut
                 di sini (dulu ya, dan itu berarti menunggu puluhan panggilan AI
                 sekaligus) - sekarang dipisah per pelajar lewat tombol di kolom
                 "Buat Remedial".

                 Setiap form punya scope Alpine sendiri yang berisi form + overlay-nya.
                 Kalau overlay ditaruh di luar x-data, state `generating`-nya tidak
                 terjangkau dan overlay tidak pernah muncul. --}}
            <div x-data="{ generating: false }">
                <form
                    action="{{ route('pengajar.prediksi.run') }}"
                    method="POST"
                    @submit="generating = true"
                >
                    @csrf
                    <x-btn-primary type="submit" size="sm" x-bind:disabled="generating">
                        <svg x-show="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg x-show="generating" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-show="!generating">{{ __('guru_analitik.prediksi.run') }}</span>
                        <span x-show="generating" x-cloak>{{ __('guru_analitik.prediksi.calculating') }}</span>
                    </x-btn-primary>
                </form>

                <x-loading-overlay
                    :judul="__('guru_analitik.prediksi.overlay_risk')"
                    :heading="__('guru_analitik.prediksi.overlay_heading')"
                    :catatan="__('guru_analitik.prediksi.overlay_note')"
                />
            </div>

            {{-- Tombol "Latih Ulang Model" SENGAJA disembunyikan (tidak dipindah ke
                 panel admin): skor risiko sekarang deterministik (Skor Penyimpangan
                 dari Baseline) - tidak ada model klasifikasi yang perlu dilatih
                 ulang. Route POST pengajar.prediksi.retrain tetap ada dan masih
                 diuji; hanya UI-nya yang tidak ditampilkan. --}}
        </x-slot:actions>
    </x-page-header>

    <div x-data="{ generating: false }">
    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <x-th align="left">{{ __('guru_analitik.label.student') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.label.class') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.prediksi.model_result') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.prediksi.score_threshold') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.prediksi.risk_score') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.prediksi.remedial') }}</x-th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($siswaList as $siswa)
                    <x-tr>
                        <x-td class="font-medium text-slate-900 dark:text-slate-100">
                            <a href="{{ route('pengajar.prediksi.show', ['siswa' => $siswa->id]) }}" class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 hover:underline">
                                {{ $siswa->name }}
                            </a>
                        </x-td>
                        <x-td>
                            {{ $siswa->kelasDiikuti->pluck('nama_kelas')->join(', ') }}
                        </x-td>
                        <x-td>
                            @php $pred = $siswa->prediksiAi->first(); @endphp
                            @if($pred)
                                <x-badge
                                    variant="{{ $pred->hasil === 'aman' ? 'success' : ($pred->hasil === 'perlu_pantau' ? 'warning' : 'danger') }}"
                                >
                                    {{ $pred->hasil }}
                                </x-badge>
                            @else
                                <span class="text-slate-500 dark:text-slate-400">-</span>
                            @endif
                        </x-td>
                        <x-td>
                            @if($pred)
                                <x-badge
                                    variant="{{ $pred->labelAmbang() === 'aman' ? 'success' : ($pred->labelAmbang() === 'perlu_pantau' ? 'warning' : 'danger') }}"
                                >
                                    {{ $pred->labelAmbang() }}
                                </x-badge>
                            @else
                                <span class="text-slate-500 dark:text-slate-400">-</span>
                            @endif
                        </x-td>
                        <x-td>
                            {{ $pred ? number_format($pred->skor_risiko, 1) : '-' }}
                        </x-td>
                        <x-td>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('pengajar.prediksi.remedial', $siswa) }}" method="POST" @submit="generating = true">
                                    @csrf
                                    <x-btn-primary type="submit" size="sm" x-bind:disabled="generating">
                                        {{ __('guru_analitik.prediksi.remedial') }}
                                    </x-btn-primary>
                                </form>
                                @if(in_array($siswa->id, $butuhRemedial, true))
                                    <x-badge variant="warning">{{ __('guru_analitik.prediksi.remedial_flagged') }}</x-badge>
                                @endif
                            </div>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="6" class="py-6">
                            {{ __('guru_analitik.prediksi.no_student') }}
                        </x-td>
                    </x-tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">
                {{ __('guru_analitik.prediksi.remedial_hint') }}
            </p>
            {{ $siswaList->links() }}
        </div>
    </x-card>

    {{-- Overlay untuk tombol "Buat Remedial" per pelajar. Pembuatan remedial
         kini sinkron (satu pelajar per request), jadi guru perlu indikator
         tunggu yang jelas. --}}
    <x-loading-overlay
        :judul="__('guru_analitik.prediksi.remedial_overlay')"
        :heading="__('guru_analitik.prediksi.overlay_heading')"
        :catatan="__('guru_analitik.prediksi.remedial_note')"
    />
    </div>
@endsection
