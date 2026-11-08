@extends('layouts.app')

@section('title', __('operator.materi.class_page_title', ['kelas' => $kelas->nama_kelas]))

@section('content')
    @if($kelas->mataPelajaran)
        <x-back-link href="{{ route('operator.mata-pelajaran.show', $kelas->mataPelajaran) }}" :label="$kelas->mataPelajaran->nama" />
    @else
        <x-back-link href="{{ route('operator.kelas.index') }}" :label="__('operator.materi.class_list_back')" />
    @endif

    <x-page-header
        :title="__('operator.materi.class_materials_title', ['kelas' => $kelas->nama_kelas])"
        :subtitle="__('operator.materi.class_materials_subtitle')"
    />

    @php
        $headers = [
            __('operator.materi.column_material'),
            __('operator.materi.column_meeting'),
            __('operator.materi.column_teacher'),
            __('operator.materi.column_file'),
        ];
    @endphp

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <x-table :headers="$headers">
                @forelse($materiList as $materi)
                    <x-tr>
                        <x-td>
                            <a href="{{ route('operator.materi.show', $materi) }}" class="inline-flex min-h-11 items-center font-medium text-emerald-700 hover:underline dark:text-emerald-400">
                                {{ $materi->judul }}
                            </a>
                        </x-td>
                        <x-td>{{ $materi->pertemuan?->nama_pertemuan ?? '-' }}</x-td>
                        <x-td>{{ $materi->guru?->name ?? '-' }}</x-td>
                        <x-td>{{ $materi->file_path ? strtoupper($materi->file_type ?: __('operator.materi.file_fallback')) : __('operator.materi.no_file') }}</x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="4" class="py-6">
                            {{ __('operator.materi.empty_class_materials') }}
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
