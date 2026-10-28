@php $title = __('siswa.remedial.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6 max-w-4xl mx-auto">
    <x-back-link href="{{ route('pelajar.remedial.index') }}" />

    <x-card padding="false">
        <div class="p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-1">
                @if($remedial->status === 'belum_dibaca')
                    <x-badge variant="baru" dot="true">{{ __('siswa.shared.status_baru') }}</x-badge>
                @elseif($remedial->status === 'selesai')
                    <x-badge variant="dibaca" dot="true">{{ __('siswa.shared.status_sudah_dikerjakan') }}</x-badge>
                @else
                    <x-badge variant="dibaca" dot="true">{{ __('siswa.shared.status_sudah_dibaca') }}</x-badge>
                @endif
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $remedial->generated_at?->format('d M Y H:i') ?: '-' }}
                </span>
            </div>

            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-3 mb-4">
                {{ $remedial->topik_lemah ?: __('siswa.remedial.title') }}
            </h1>

            <div class="mb-4">
                <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                    {{ __('siswa.remedial.ringkasan_materi') }}
                </h2>
                <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                    {{ $remedial->ringkasan }}
                </div>
            </div>

            @if(empty($remedial->tugas_remedial) && $remedial->latihan)
                <div class="border-t pt-6">
                    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                        {{ __('siswa.remedial.latihan_soal') }}
                    </h2>
                    @if(! empty($remedial->latihan_items))
                        <x-remedial-latihan :items="$remedial->latihan_items" />
                    @else
                        <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                            {{ $remedial->latihan }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-card>

    @if(! empty($remedial->bloom_tujuan))
        <x-card padding="true" class="mt-4">
            <h2 class="font-semibold text-slate-900 dark:text-slate-100 mb-4">
                {{ __('siswa.remedial.tujuan_pembelajaran') }}
                <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">{{ __('siswa.remedial.taksonomi_bloom') }}</span>
            </h2>
            <ol class="space-y-2">
                @foreach($remedial->bloom_tujuan as $t)
                    @php
                        $kode = strtoupper((string) ($t['kode'] ?? ''));
                    @endphp
                    <li class="flex items-start gap-2">
                        <span class="inline-flex items-center shrink-0 rounded-md text-xs font-bold px-2 py-1
                            {{ match($kode) {
                                'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                'C2' => 'bg-blue-50 text-blue-700',
                                'C3' => 'bg-emerald-50 text-emerald-700',
                                'C4' => 'bg-purple-50 text-purple-700',
                                'C5' => 'bg-amber-50 text-amber-700',
                                'C6' => 'bg-green-50 text-green-700',
                                default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                            } }}">
                            {{ $kode }}
                        </span>
                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            <span class="font-medium block text-slate-800 dark:text-slate-200">{{ $t['level'] ?? '-' }}</span>
                            {{ $t['tujuan'] ?? '' }}
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-card>
    @endif

    @if(! empty($remedial->tugas_remedial))
        <x-card padding="true" class="mt-4">
            <h2 class="font-semibold text-slate-900 dark:text-slate-100 mb-1">{{ __('siswa.remedial.tugas_remedial') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                {{ __('siswa.remedial.catatan_penilaian') }}
            </p>

            @if($remedial->hasil_remedial)
                <div class="mb-4 p-4 rounded-lg border
                    {{ $remedial->bloom_tercapai ? 'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-800' : 'bg-amber-50 border-amber-200' }}">
                    <p class="font-semibold {{ $remedial->bloom_tercapai ? 'text-green-700 dark:text-green-300' : 'text-amber-700' }}">
                        {{ $remedial->bloom_tercapai
                            ? __('siswa.remedial.tujuan_tercapai')
                            : __('siswa.remedial.tujuan_belum_tercapai') }}
                    </p>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 whitespace-pre-wrap">
                        {{ $remedial->mastery_alasan }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        {{ __('siswa.remedial.dinilai_ai', ['tanggal' => $remedial->dinilai_at?->format('d M Y H:i') ?: '-']) }}
                    </p>
                    <form method="POST" action="{{ route('pelajar.remedial.reset', $remedial) }}" class="mt-3">
                        @csrf
                        <x-btn-secondary type="submit" size="sm">{{ __('siswa.remedial.kerjakan_ulang') }}</x-btn-secondary>
                    </form>
                </div>

                @foreach($remedial->tugas_remedial as $i => $s)
                    @php
                        $hasil = is_array($remedial->hasil_remedial ?? null)
                            ? collect($remedial->hasil_remedial)->firstWhere('indeks', $i)
                            : null;
                        $kode = strtoupper((string) ($s['bloom'] ?? ''));
                    @endphp
                    <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-2">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $i + 1 }}. <x-math :value="$s['pertanyaan'] ?? __('siswa.shared.soal_fallback')" /></span>
                            <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded
                                {{ match($kode) {
                                    'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                    'C2' => 'bg-blue-50 text-blue-600',
                                    'C3' => 'bg-emerald-50 text-emerald-700',
                                    'C4' => 'bg-purple-50 text-purple-600',
                                    'C5' => 'bg-amber-50 text-amber-600',
                                    'C6' => 'bg-green-50 text-green-700',
                                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                } }}">
                                {{ $kode }} — {{ $s['bloom'] ?? '' }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 whitespace-pre-wrap mt-1">
                            <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.shared.jawabanmu') }}:</span> <x-math :value="$remedial->jawaban_remedial[$i] ?? ''" />
                        </p>
                        @if($hasil)
                            @php
                                $variant = match($hasil['status'] ?? '') {
                                    'benar' => 'verified',
                                    'mendekati' => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <div class="flex items-center gap-2 mt-2">
                                <x-badge :variant="$variant">{{ ucfirst($hasil['status'] ?? 'mendekati') }}</x-badge>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.remedial.skor', ['skor' => $hasil['skor'] ?? '-']) }}/100</span>
                                @if(! empty($hasil['alasan']))
                                    <span class="text-xs text-slate-500 dark:text-slate-400">— {{ $hasil['alasan'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            @elseif($remedial->jawaban_remedial)
                <div class="p-4 rounded-lg border border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-500/10 text-sm text-blue-700 dark:text-blue-300">
                    {{ __('siswa.remedial.menunggu_pemeriksaan') }}
                </div>
                @foreach($remedial->tugas_remedial as $i => $s)
                    <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-2 mt-3">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $i + 1 }}. <x-math :value="$s['pertanyaan'] ?? __('siswa.shared.soal_fallback')" /></p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.shared.jawabanmu') }}:</span> <x-math :value="$remedial->jawaban_remedial[$i] ?? ''" />
                        </p>
                    </div>
                @endforeach
            @else
                <form method="POST" action="{{ route('pelajar.remedial.submit', $remedial) }}">
                    @csrf
                    @foreach($remedial->tugas_remedial as $i => $s)
                        @php
                            $kode = strtoupper((string) ($s['bloom'] ?? ''));
                        @endphp
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">
                                {{ $i + 1 }}. <x-math :value="$s['pertanyaan'] ?? __('siswa.shared.soal_fallback')" />
                            </label>
                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded mb-2
                                {{ match($kode) {
                                    'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                    'C2' => 'bg-blue-50 text-blue-600',
                                    'C3' => 'bg-emerald-50 text-emerald-700',
                                    'C4' => 'bg-purple-50 text-purple-600',
                                    'C5' => 'bg-amber-50 text-amber-600',
                                    'C6' => 'bg-green-50 text-green-700',
                                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                } }}">
                                {{ __('siswa.shared.level', ['kode' => $kode]) }}
                            </span>
                            <textarea
                                name="jawaban[{{ $i }}]"
                                rows="4"
                                required
                                placeholder="{{ __('siswa.shared.placeholder_jawaban') }}"
                                class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            ></textarea>
                        </div>
                    @endforeach
                    @error('jawaban')
                        <p class="text-red-600 dark:text-red-400 text-sm mb-2">{{ $message }}</p>
                    @enderror
                    <x-btn-primary type="submit" size="sm">{{ __('siswa.shared.kumpulkan_jawaban') }}</x-btn-primary>
                </form>
            @endif
        </x-card>
    @endif

    <div class="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
        <span>{{ __('siswa.remedial.dibuat_oleh', ['provider' => $remedial->provider_llm ?: '-']) }}</span>
        @if($remedial->prediksiAi)
            <span>{{ __('siswa.remedial.skor_risiko', ['skor' => $remedial->prediksiAi->skor_risiko ?? '-']) }}</span>
        @endif
    </div>
</div>
@endsection