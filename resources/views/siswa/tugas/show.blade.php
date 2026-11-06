@php $title = $tugas->judul; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-back-link href="{{ route('pelajar.tugas.index') }}" />

    <div class="flex items-center gap-3 mt-2">
        <h1 class="text-xl font-bold">{{ $tugas->judul }}</h1>
        @if(!$tugas->batas_jam && $tugas->isLewatDeadline())
            <x-badge variant="danger">{{ __('siswa.shared.status_terlambat') }}</x-badge>
        @endif
        @if($tugas->batas_jam && $pengumpulan && $pengumpulan->terlambat)
            <x-badge variant="danger">{{ __('siswa.shared.status_terlambat') }}</x-badge>
        @endif
    </div>
    <p class="text-slate-500 dark:text-slate-400">{{ $tugas->kelas?->nama_kelas }}</p>
    @if($tugas->batas_jam)
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Waktu pengerjaan: <strong>{{ $tugas->batas_jam }} jam</strong> (dihitung setelah klik "Mulai Kerjakan")
        </p>
    @elseif($tugas->deadline)
        <p class="text-sm {{ $tugas->isLewatDeadline() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-slate-500 dark:text-slate-400' }}">
            {{ __('siswa.shared.deadline', ['deadline' => $tugas->deadline->format('d M Y H:i')]) }}
            @if($tugas->isLewatDeadline())
                {{ __('siswa.tugas.sudah_lewat') }}
            @endif
        </p>
    @endif

    @if(!$tugas->batas_jam && $tugas->isLewatDeadline() && !$pengumpulan)
        <div class="mt-4 bg-red-50 dark:bg-red-500/10 rounded-lg border border-red-200 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-400">
            {{ __('siswa.tugas.terlambat_notif_1') }} <strong>{{ __('siswa.shared.status_terlambat') }}</strong>{{ __('siswa.tugas.terlambat_notif_2') }}
        </div>
    @endif

    <x-card padding="true" class="mt-4">
        <h2 class="font-semibold mb-2">{{ __('siswa.tugas.deskripsi') }}</h2>
        <p class="whitespace-pre-wrap">{{ $tugas->deskripsi ?: __('siswa.tugas.tidak_ada_deskripsi') }}</p>
    </x-card>

    @if($pengumpulan?->sudahDikumpulkan())
        <div class="mt-4 {{ $pengumpulan->terlambat ? 'bg-red-50 border-red-200 dark:border-red-800' : 'bg-green-50 dark:bg-green-500/10 border-green-200' }} rounded-lg shadow-sm border p-4">
            <div class="flex items-center gap-2">
                <h2 class="font-semibold {{ $pengumpulan->terlambat ? 'text-red-700 dark:text-red-400' : 'text-green-700 dark:text-green-400' }}">
                    {{ $pengumpulan->terlambat ? __('siswa.tugas.dikumpulkan_terlambat') : __('siswa.tugas.sudah_dikumpulkan') }}
                </h2>
                @if($pengumpulan->terlambat)
                    <x-badge variant="danger">{{ __('siswa.shared.status_terlambat') }}</x-badge>
                @endif
            </div>
            @if($pengumpulan->nilai !== null)
                <p class="mt-1">{{ __('siswa.shared.nilai') }}: <strong>{{ $pengumpulan->nilai }}</strong></p>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('siswa.tugas.belum_dinilai') }}</p>
            @endif
        </div>

        @if($tugas->tipe === 'essai' && $tugas->soal)
            <x-card padding="true" class="mt-4">
                <h2 class="font-semibold mb-3">{{ __('siswa.shared.jawabanmu') }}</h2>
                @if($pengumpulan->koreksi_ai_gagal)
                    <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                        {{ __('siswa.tugas.pemeriksaan_ai_gagal') }}
                    </p>
                @endif
                @foreach($soalTampil as $s)
                    <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-2">
                        <div class="flex items-start gap-2">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 flex-1">{{ $s['nomor'] }}. <x-math :value="$s['pertanyaan']" /></p>
                            @if($s['bloom'])
                                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ $s['bloom'] }}</span>
                            @endif
                        </div>
                        @if(! empty($s['jawaban']))
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 whitespace-pre-wrap"><x-math :value="$s['jawaban']" /></p>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400 italic mt-1">{{ __('siswa.tugas.tidak_dijawab') }}</p>
                        @endif
                        @if($s['hasil'])
                            <div class="flex items-center gap-2 mt-2">
                                <x-badge :variant="$s['varian']">{{ $s['label'] }}</x-badge>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.tugas.skor_ai', ['skor' => $s['skor'] ?? '-']) }}/100</span>
                                @if($s['alasan'])
                                    <span class="text-xs text-slate-500 dark:text-slate-400">— {{ $s['alasan'] }}</span>
                                @endif
                            </div>
                        @else
                            <p class="text-xs {{ $pengumpulan->koreksi_ai_gagal ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-slate-400' }} mt-2 italic">
                                {{ $pengumpulan->koreksi_ai_gagal ? __('siswa.tugas.pemeriksaan_ai_gagal') : __('siswa.tugas.menunggu_pemeriksaan') }}
                            </p>
                        @endif
                    </div>
                @endforeach
                @if($pengumpulan->nilai_ai !== null && $pengumpulan->nilai === null)
                    <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-2">{{ __('siswa.tugas.nilai_saran_ai') }}: <strong>{{ $pengumpulan->nilai_ai }}</strong> ({{ __('siswa.tugas.menunggu_konfirmasi_guru') }})</p>
                @endif
            </x-card>
        @endif
    @else
        @if($tugas->batas_jam && (!$pengumpulan || !$pengumpulan->dimulai_pada))
            <x-card padding="true" class="mt-4">
                <h2 class="font-semibold mb-2">{{ __('siswa.tugas.kumpulkan_tugas') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">
                    Tugas ini memiliki batas waktu pengerjaan <strong>{{ $tugas->batas_jam }} jam</strong>. Waktu akan dihitung mundur setelah kamu klik tombol "Mulai Kerjakan".
                </p>
                <form method="POST" action="{{ route('pelajar.tugas.mulai', $tugas) }}">
                    @csrf
                    <x-btn-primary type="submit" size="sm">
                        Mulai Kerjakan
                    </x-btn-primary>
                </form>
            </x-card>
        @else
            <x-card padding="true" class="mt-4">
                <h2 class="font-semibold mb-3">{{ __('siswa.tugas.kumpulkan_tugas') }}</h2>
                @if($tugas->batas_jam && $pengumpulan && $pengumpulan->dimulai_pada && $pengumpulan->batas_kerjakan)
                    <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800">
                        <p class="text-sm text-amber-800 dark:text-amber-300 font-medium">
                            Batas pengumpulan: <span>{{ $pengumpulan->batas_kerjakan->format('d M Y H:i') }}</span>
                        </p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Perhatikan sisa waktu saat mengerjakan. Jika melewati batas waktu, status akan dihitung terlambat.</p>
                    </div>
                @endif
                <form
                    method="POST"
                    action="{{ route('pelajar.tugas.submit', $tugas) }}"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @if($tugas->tipe === 'upload')
                        <input
                            type="file"
                            name="file"
                            class="w-full border rounded px-3 py-2"
                            accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.png"
                            required
                        >
                        @error('file')
                            <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    @elseif($tugas->tipe === 'pilihan_ganda' && $tugas->soal)
                        @foreach($soalTampil as $i => $s)
                            <div class="mb-3">
                                <div class="flex items-start gap-2">
                                    <p class="font-medium flex-1">{{ $s['nomor'] }}. <x-math :value="$s['pertanyaan']" /></p>
                                    @if($s['bloom'])
                                        <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ $s['bloom'] }}</span>
                                    @endif
                                </div>
                                @foreach($s['opsi'] as $key => $val)
                                    <label class="block ml-4">
                                        <input
                                            type="radio"
                                            name="jawaban[{{ $i }}]"
                                            value="{{ $key }}"
                                            required
                                        >
                                        <x-math :value="$val" />
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    @elseif($tugas->tipe === 'essai' && $tugas->soal)
                        @foreach($soalTampil as $i => $s)
                            <div class="mb-4">
                                <div class="flex items-start gap-2 mb-1">
                                    <label class="block font-medium text-slate-900 dark:text-slate-100 flex-1">{{ $s['nomor'] }}. <x-math :value="$s['pertanyaan']" /></label>
                                    @if($s['bloom'])
                                        <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ $s['bloom'] }}</span>
                                    @endif
                                </div>
                                <textarea
                                    name="jawaban[{{ $i }}]"
                                    rows="4"
                                    required
                                    placeholder="{{ __('siswa.shared.placeholder_jawaban') }}"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                ></textarea>
                            </div>
                        @endforeach
                    @endif
                    @error('jawaban')
                        <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <x-btn-primary type="submit" size="sm" class="mt-2">
                        {{ __('siswa.tugas.kumpulkan') }}
                    </x-btn-primary>
                </form>
            </x-card>
        @endif
    @endif
</div>
@endsection