@extends('layouts.app')

@section('title', __('guru_analitik.nilai.title_student', ['nama' => $siswa->name]) . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_analitik.nilai.heading_student', ['nama' => $siswa->name])"
        :subtitle="$kelasDipilih ? $kelasDipilih->nama_kelas : __('guru_analitik.nilai.all_my_classes')"
    >
        <x-slot:actions>
            <a href="{{ route('pengajar.nilai', array_filter(['kelas' => $kelasDipilih?->id])) }}">
                <x-btn-secondary>{{ __('common.back') }}</x-btn-secondary>
            </a>
            <a href="{{ route('pengajar.nilai.pdf', array_filter(['siswa' => $siswa->id, 'kelas' => $kelasDipilih?->id])) }}">
                <x-btn-primary>{{ __('guru_analitik.nilai.print_pdf') }}</x-btn-primary>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <x-card class="!p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('guru_analitik.nilai.rata_kuis') }}</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $rataKuis === null ? '-' : number_format($rataKuis, 1) }}</p>
        </x-card>
        <x-card class="!p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('guru_analitik.nilai.rata_tugas') }}</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $rataTugas === null ? '-' : number_format($rataTugas, 1) }}</p>
        </x-card>
        <x-card class="!p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('guru_analitik.nilai.rata_total') }}</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $rataTotal === null ? '-' : number_format($rataTotal, 1) }}</p>
        </x-card>
        <x-card class="!p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('guru_analitik.nilai.nilai_terisi') }}</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $jumlahDinilai }}</p>
        </x-card>
    </div>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <x-th align="left">{{ __('guru_analitik.label.type') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.label.title') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.label.class') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.label.meeting') }}</x-th>
                    <x-th align="center">{{ __('guru_analitik.label.grade') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.label.date') }}</x-th>
                    <x-th align="left">{{ __('guru_analitik.label.notes') }}</x-th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($rows as $row)
                    <x-tr>
                        <x-td>
                            <x-badge variant="{{ $row['jenis'] === 'Kuis' ? 'info' : 'success' }}">
                                {{ $row['jenis'] }}
                            </x-badge>
                        </x-td>
                        <x-td class="font-medium text-slate-900 dark:text-slate-100">{{ $row['judul'] }}</x-td>
                        <x-td>{{ $row['kelas'] ?? '-' }}</x-td>
                        <x-td>{{ $row['pertemuan'] ?? '-' }}</x-td>
                        <x-td align="center">
                            @if($row['nilai'] === null)
                                <x-badge variant="warning">{{ __('guru_analitik.nilai.not_graded') }}</x-badge>
                            @else
                                <span @class([
                                    'rounded px-2 py-0.5 text-xs font-semibold',
                                    'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' => $row['nilai'] < 60,
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' => $row['nilai'] >= 60 && $row['nilai'] < 75,
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-300' => $row['nilai'] >= 75,
                                ])>{{ number_format($row['nilai'], 1) }}</span>
                            @endif
                        </x-td>
                        <x-td>{{ $row['tanggal']?->format('d/m/Y') ?? '-' }}</x-td>
                        <x-td>
                            @if($row['catatan'])
                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ $row['catatan'] }}</span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="7" class="py-6">
                            {{ __('guru_analitik.nilai.no_score_for_student') }}
                        </x-td>
                    </x-tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
@endsection
