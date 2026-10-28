@extends('layouts.app')

@section('title', __('navigation.assignments') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_soal.daftar_tugas')"
        :subtitle="__('guru_soal.subtitle_daftar_tugas')"
    />

    @if ($tugasBelumDinilai->isNotEmpty())
        <div class="mb-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-500/10 p-4">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex-shrink-0 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h12.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0l-7 12a2 2 0 001.74 3z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                        {{ __('guru_soal.belum_dinilai', ['jumlah' => $tugasBelumDinilai->sum('belum_dinilai')]) }}
                    </p>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                        {{ __('guru_soal.peringatan_belum_dinilai') }}
                    </p>
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($tugasBelumDinilai as $t)
                            <li>
                                <a href="{{ route('pengajar.tugas.penilaian', $t) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-900 px-2.5 py-1 text-xs font-medium text-amber-900 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-500/20">
                                    {{ Str::limit($t->judul, 40) }}
                                    <span class="rounded bg-amber-200 dark:bg-amber-500/30 px-1.5 py-0.5 text-[11px] font-semibold">
                                        {{ $t->belum_dinilai }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <x-th align="left">{{ __('guru_soal.judul') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.kelas') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.pertemuan') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.deadline') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.pengumpulan') }}</x-th>
                    <x-th align="left">{{ __('common.actions') }}</x-th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($tugasList as $tugas)
                    <x-tr>
                        <x-td class="font-medium text-slate-900 dark:text-slate-100">{{ $tugas->judul }}</x-td>
                        <x-td>{{ $tugas->kelas?->nama_kelas ?? '-' }}</x-td>
                        <x-td>{{ $tugas->pertemuan?->nama_pertemuan ?? '-' }}</x-td>
                        <x-td>{{ $tugas->deadline ? $tugas->deadline->format('d M Y') : '-' }}</x-td>
                        <x-td>
                            <span class="text-sm {{ $tugas->pengumpulan_count > 0 ? 'text-green-800 dark:text-green-400 font-medium' : 'text-slate-500 dark:text-slate-400' }}">
                                {{ __('guru_soal.jumlah_siswa', ['jumlah' => $tugas->pengumpulan_count]) }}
                            </span>
                        </x-td>
                        <x-td class="flex gap-2">
                            <x-btn-secondary href="{{ route('pengajar.tugas.show', $tugas) }}" size="sm">
                                {{ __('common.detail') }}
                            </x-btn-secondary>
                            <x-btn-secondary href="{{ route('pengajar.tugas.penilaian', $tugas) }}" size="sm" color="emerald">
                                {{ __('guru_soal.nilai') }}
                            </x-btn-secondary>
                            <x-btn-secondary href="{{ route('pengajar.tugas.edit', $tugas) }}" size="sm">
                                {{ __('guru_soal.edit') }}
                            </x-btn-secondary>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="6" class="py-6">{{ __('guru_soal.kosong_tugas') }}</x-td>
                    </x-tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $tugasList->links() }}</div>
    </x-card>
@endsection
