@php $title = $kuis->judul; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-back-link href="{{ route('pelajar.kuis.index') }}" />

    <h1 class="text-xl font-bold mt-2">{{ $kuis->judul }}</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.shared.durasi') }}: {{ $kuis->durasi_menit }} {{ __('siswa.shared.menit') }}</p>

    <div
        x-data="{ timeLeft: {{ $kuis->durasi_menit * 60 }}, timer: null }"
        x-init="timer = setInterval(() => { timeLeft--; if(timeLeft <= 0) { clearInterval(timer); $el.closest('form').submit(); } }, 1000)"
        class="mt-4"
    >
        <p class="text-sm font-medium">
            {{ __('siswa.kuis.sisa_waktu') }}
            <span x-text="Math.floor(timeLeft / 60) + ':' + String(timeLeft % 60).padStart(2, '0')"></span>
        </p>

        <x-card padding="true" class="mt-4">
            <form method="POST" action="{{ route('pelajar.kuis.submit', $kuis) }}">
                @csrf
                @foreach($kuis->soal as $i => $s)
                    @php
                        $opsi = $s['opsi'] ?? null;
                        if (!is_array($opsi) || count($opsi) < 2) {
                            $opsi = is_array($s['pilihan'] ?? null) ? $s['pilihan'] : [];
                            $opsi = array_values($opsi);
                        }
                        $bloom = strtoupper(trim((string) ($s['bloom'] ?? '')));
                    @endphp
                    <div class="mb-4 p-3 border rounded">
                        <div class="flex items-start gap-2 mb-2">
                            <p class="font-medium flex-1">{{ $i + 1 }}. <x-math :value="$s['pertanyaan'] ?? __('siswa.shared.soal_fallback')" /></p>
                            @if(in_array($bloom, ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], true))
                                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ $bloom }}
                                </span>
                            @endif
                        </div>
                        @foreach($opsi as $key => $val)
                            <label class="block ml-4">
                                <input
                                    type="radio"
                                    name="jawaban[{{ $i }}]"
                                    value="{{ $key }}"
                                    required
                                >
                                <span class="ml-1"><x-math :value="$val" /></span>
                            </label>
                        @endforeach
                    </div>
                @endforeach
                <x-btn-primary type="submit" size="sm">
                    {{ __('siswa.shared.kumpulkan_jawaban') }}
                </x-btn-primary>
            </form>
        </x-card>
    </div>
</div>
@endsection