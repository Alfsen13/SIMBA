@extends('layouts.app')

@section('title', $mataPelajaran->nama . ' - Operator')

@section('content')
    <x-back-link href="{{ route('operator.mata-pelajaran.index') }}" label="Daftar Mata Pelajaran" />

    <x-page-header
        :title="$mataPelajaran->nama"
        subtitle="Daftar kelas pada mata pelajaran ini"
    />

    @php
        $headers = ['Kelas', 'Pengajar', 'Materi'];
    @endphp

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <x-table :headers="$headers">
                @forelse($kelasList as $kelas)
                    <x-tr>
                        <x-td>
                            <a href="{{ route('operator.kelas.materi', $kelas) }}" class="font-medium text-emerald-700 hover:underline dark:text-emerald-400">
                                {{ $kelas->nama_kelas }}
                            </a>
                        </x-td>
                        <x-td>{{ $kelas->guru?->name ?? '-' }}</x-td>
                        <x-td>{{ $kelas->materi_count }}</x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="3" class="py-6">
                            Belum ada kelas pada mata pelajaran ini.
                        </x-td>
                    </x-tr>
                @endforelse
            </x-table>
        </div>
        @if($kelasList->hasPages())
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                {{ $kelasList->links() }}
            </div>
        @endif
    </x-card>
@endsection
