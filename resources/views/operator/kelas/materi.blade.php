@extends('layouts.app')

@section('title', 'Materi ' . $kelas->nama_kelas . ' - Operator')

@section('content')
    @if($kelas->mataPelajaran)
        <x-back-link href="{{ route('operator.mata-pelajaran.show', $kelas->mataPelajaran) }}" :label="$kelas->mataPelajaran->nama" />
    @else
        <x-back-link href="{{ route('operator.kelas.index') }}" label="Daftar Kelas" />
    @endif

    <x-page-header
        :title="'Materi - ' . $kelas->nama_kelas"
        subtitle="Materi yang diunggah pengajar untuk kelas ini"
    />

    @php
        $headers = ['Materi', 'Pertemuan', 'Pengajar', 'Berkas'];
    @endphp

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <x-table :headers="$headers">
                @forelse($materiList as $materi)
                    <x-tr>
                        <x-td>
                            <a href="{{ route('operator.materi.show', $materi) }}" class="font-medium text-emerald-700 hover:underline dark:text-emerald-400">
                                {{ $materi->judul }}
                            </a>
                        </x-td>
                        <x-td>{{ $materi->pertemuan?->nama_pertemuan ?? '-' }}</x-td>
                        <x-td>{{ $materi->guru?->name ?? '-' }}</x-td>
                        <x-td>{{ $materi->file_path ? strtoupper($materi->file_type ?? 'berkas') : 'Tidak ada berkas' }}</x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="4" class="py-6">
                            Belum ada materi yang diunggah pengajar.
                        </x-td>
                    </x-tr>
                @endforelse
            </x-table>
        </div>
        @if($materiList->hasPages())
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                {{ $materiList->links() }}
            </div>
        @endif
    </x-card>
@endsection
