@extends('layouts.app')

@section('title', __('guru_soal.title_penilaian') . ' - ' . $tugas->judul . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.tugas.index') }}"
        :label="__('common.back')"
    />

    <x-page-header
        :title="__('guru_soal.penilaian_tugas')"
        subtitle="{{ $tugas->judul }} &middot; {{ $tugas->kelas?->nama_kelas }}"
    />

    @if($tugas->tipe === 'essai')
        <div class="mb-4 flex items-center gap-3">
            <form action="{{ route('pengajar.tugas.periksa-ai', $tugas) }}" method="POST">
                @csrf
                <x-btn-secondary type="submit" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ __('guru_soal.periksa_ulang_ai') }}
                </x-btn-secondary>
            </form>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_soal.help_periksa_ulang') }}</p>
        </div>

        <form
            action="{{ route('pengajar.tugas.nilai', $tugas) }}"
            method="POST"
        >
            @csrf
            @forelse($tugas->pengumpulan as $p)
                @php $baris = $presenter->barisPenilaian($tugas->soal ?? [], $p); @endphp
                <x-card class="mb-4">
                    @if($p->koreksi_ai_gagal)
                        <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                            {{ __('guru_soal.pemeriksaan_ai_gagal') }}
                        </p>
                    @endif
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $p->siswa?->name ?? __('guru_soal.siswa') }}</span>
                            @if($p->terlambat)
                                <x-badge variant="danger">{{ __('guru_soal.terlambat') }}</x-badge>
                            @else
                                <x-badge variant="verified">{{ __('guru_soal.tepat_waktu') }}</x-badge>
                            @endif
                        </div>
                        @if($p->nilai_ai !== null)
                            <x-badge variant="info" class="text-sm">{{ __('guru_soal.nilai_ai', ['nilai' => $p->nilai_ai]) }}</x-badge>
                        @endif
                    </div>

                    @foreach($baris as $s)
                        <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-2">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $s['nomor'] }}. <x-math :value="$s['pertanyaan']" /></p>
                            @if(! empty($s['jawaban']))
                                <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 whitespace-pre-wrap"><x-math :value="$s['jawaban']" /></p>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic mt-1">{{ __('guru_soal.tidak_dijawab') }}</p>
                            @endif
                            @if($s['hasil'])
                                <div class="flex items-center gap-2 mt-2">
                                    <x-badge :variant="$s['varian']">
                                        {{ $s['label'] }}
                                    </x-badge>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_soal.skor_ai', ['skor' => $s['skor'] ?? '-']) }}/100</span>
                                    @if($s['alasan'])
                                        <span class="text-xs text-slate-500 dark:text-slate-400">— {{ $s['alasan'] }}</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs {{ $p->koreksi_ai_gagal ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-slate-400' }} mt-2 italic">
                                    {{ $p->koreksi_ai_gagal ? __('guru_soal.pemeriksaan_ai_gagal') : __('guru_soal.menunggu_pemeriksaan') }}
                                </p>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex items-end gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_soal.nilai_final') }}</label>
                            <input
                                type="number"
                                name="nilai[{{ $p->id }}]"
                                value="{{ old('nilai.' . $p->id, $p->nilai) }}"
                                min="0"
                                max="100"
                                class="w-24 rounded-lg border-slate-300 dark:border-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                            >
                        </div>
                        @if($p->nilai_ai !== null)
                            <button
                                type="button"
                                onclick="this.closest('form').querySelector('input[name*=\'nilai[{{ $p->id }}]\']')?.setAttribute('value', {{ $p->nilai_ai }})"
                                class="text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 pb-0.5"
                            >
                                {{ __('guru_soal.gunakan_nilai_ai') }}
                            </button>
                        @endif
                    </div>
                </x-card>
            @empty
                <x-card>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.kosong_pengumpulan') }}</p>
                </x-card>
            @endforelse
            @if($tugas->pengumpulan->isNotEmpty())
                <div class="mt-4">
                    <x-btn-primary type="submit">
                        {{ __('guru_soal.simpan_nilai') }}
                    </x-btn-primary>
                </div>
            @endif
        </form>
    @elseif($tugas->tipe === 'pilihan_ganda')
        <form
            action="{{ route('pengajar.tugas.nilai', $tugas) }}"
            method="POST"
        >
            @csrf
            @forelse($tugas->pengumpulan as $p)
                @php $baris = $presenter->barisPilihanGanda($tugas->soal ?? [], $p); @endphp
                <x-card class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $p->siswa?->name ?? __('guru_soal.siswa') }}</span>
                            @if($p->terlambat)
                                <x-badge variant="danger">{{ __('guru_soal.terlambat') }}</x-badge>
                            @else
                                <x-badge variant="verified">{{ __('guru_soal.tepat_waktu') }}</x-badge>
                            @endif
                        </div>
                    </div>

                    @foreach($baris as $s)
                        <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-2">
                            <div class="flex items-start gap-2">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 flex-1">{{ $s['nomor'] }}. <x-math :value="$s['pertanyaan']" /></p>
                                @if($s['bloom'])
                                    <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ $s['bloom'] }}</span>
                                @endif
                            </div>
                            @if($s['jawaban'] !== null)
                                <p class="text-sm mt-1 {{ $s['benar'] ? 'text-green-800 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ __('guru_soal.jawaban_siswa') }}: {{ chr(65 + $s['jawaban']) }}.
                                    <x-math :value="$s['jawaban_teks'] ?? ''" />
                                    <x-badge :variant="$s['benar'] ? 'verified' : 'danger'" class="ml-1">{{ $s['benar'] ? __('guru_soal.jawaban_benar') : __('guru_soal.jawaban_salah') }}</x-badge>
                                </p>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic mt-1">{{ __('guru_soal.tidak_dijawab') }}</p>
                            @endif
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ __('guru_soal.kunci_jawaban') }}: {{ chr(65 + $s['kunci']) }}.
                                <x-math :value="$s['kunci_teks'] ?? ''" />
                            </p>
                        </div>
                    @endforeach

                    <div class="flex items-end gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_soal.nilai_final') }}</label>
                            <input
                                type="number"
                                name="nilai[{{ $p->id }}]"
                                value="{{ old('nilai.' . $p->id, $p->nilai) }}"
                                min="0"
                                max="100"
                                class="w-24 rounded-lg border-slate-300 dark:border-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                            >
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.kosong_pengumpulan') }}</p>
                </x-card>
            @endforelse
            @if($tugas->pengumpulan->isNotEmpty())
                <div class="mt-4">
                    <x-btn-primary type="submit">
                        {{ __('guru_soal.simpan_nilai') }}
                    </x-btn-primary>
                </div>
            @endif
        </form>
    @else
        <x-card :padding="false">
            <form
                action="{{ route('pengajar.tugas.nilai', $tugas) }}"
                method="POST"
            >
                @csrf
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <x-th align="left">{{ __('guru_soal.siswa') }}</x-th>
                            <x-th align="left">{{ __('common.status') }}</x-th>
                            <x-th align="left">{{ __('guru_soal.file') }}</x-th>
                            <x-th align="left">{{ __('guru_soal.nilai_range') }}</x-th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($tugas->pengumpulan as $p)
                            <x-tr>
                                <x-td class="font-medium text-slate-900 dark:text-slate-100">{{ $p->siswa?->name ?? __('guru_soal.unknown') }}</x-td>
                                <x-td>
                                    @if($p->terlambat)
                                        <x-badge variant="danger">{{ __('guru_soal.terlambat') }}</x-badge>
                                    @else
                                        <x-badge variant="verified">{{ __('guru_soal.tepat_waktu') }}</x-badge>
                                    @endif
                                </x-td>
                                @if($p->file)
                                    <x-td>
                                        <a
                                            href="{{ Storage::url($p->file) }}"
                                            class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                            target="_blank"
                                        >
                                            {{ __('guru_soal.download') }}
                                        </a>
                                    </x-td>
                                @else
                                    <x-td muted>-</x-td>
                                @endif
                                <x-td>
                                    <input
                                        type="number"
                                        name="nilai[{{ $p->id }}]"
                                        value="{{ old('nilai.' . $p->id, $p->nilai) }}"
                                        min="0"
                                        max="100"
                                        class="w-20 rounded-lg border-slate-300 dark:border-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                    >
                                </x-td>
                            </x-tr>
                        @empty
                            <x-tr>
                                <x-td colspan="4" align="center">{{ __('guru_soal.kosong_pengumpulan') }}</x-td>
                            </x-tr>
                        @endforelse
                    </tbody>
                </table>
                @if($tugas->pengumpulan->isNotEmpty())
                    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                        <x-btn-primary
                            type="submit"
                        >
                            {{ __('guru_soal.simpan_nilai') }}
                        </x-btn-primary>
                    </div>
                @endif
            </form>
        </x-card>
    @endif
@endsection