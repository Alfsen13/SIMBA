@php $title = __('siswa.kuis.hasil_title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-back-link href="{{ route('pelajar.kuis.index') }}" :label="__('siswa.kuis.kembali_ke_daftar')" class="min-h-11 min-w-11" />

    <h1 class="text-xl font-bold mt-2">{{ $kuis->judul }} {{ __('siswa.kuis.hasil_label') }}</h1>

    <x-card padding="true" class="mt-4 text-center">
        <p class="text-5xl font-bold {{ $hasil->skor >= 70 ? 'text-green-700' : ($hasil->skor >= 50 ? 'text-yellow-600' : 'text-red-600 dark:text-red-400') }}">
            {{ $hasil->skor }}
        </p>
        <p class="text-slate-500 dark:text-slate-400 mt-1">{{ __('siswa.kuis.dari_100') }}</p>
    </x-card>

    <x-card padding="true" class="mt-4">
        <h2 class="font-semibold mb-3">{{ __('siswa.kuis.rincian_jawaban') }}</h2>
        @foreach($kuis->soal as $i => $s)
            @php
                $opsi = $s['opsi'] ?? null;
                if (!is_array($opsi) || count($opsi) < 2) {
                    $opsi = is_array($s['pilihan'] ?? null) ? $s['pilihan'] : [];
                    $opsi = array_values($opsi);
                }
                $jawabanBenarIdx = (int) ($s['jawaban'] ?? 0);
                $bloom = strtoupper(trim((string) ($s['bloom'] ?? '')));
            @endphp
            <div class="mb-3 p-3 border rounded">
                <div class="flex items-start gap-2">
                    <p class="font-medium flex-1">{{ $i+1 }}. <x-math :value="$s['pertanyaan'] ?? __('siswa.shared.soal_fallback')" /></p>
                    @if(in_array($bloom, ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], true))
                        <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ $bloom }}
                        </span>
                    @endif
                </div>
                <p class="text-sm">
                    {{ __('siswa.shared.jawabanmu') }}:
                    <span class="font-medium">{{ $hasil->jawaban_siswa[$i] ?? '-' }}</span>
                </p>
                <p class="text-sm">
                    {{ __('siswa.kuis.jawaban_benar') }}:
                    <span class="font-medium text-green-700 dark:text-green-400"><x-math :value="$opsi[$jawabanBenarIdx] ?? $jawabanBenarIdx" /></span>
                </p>
            </div>
        @endforeach
    </x-card>
</div>
@endsection