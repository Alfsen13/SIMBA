@extends('layouts.app')

@section('title', __('operator.mata_pelajaran.detail_page_title', ['judul' => $mataPelajaran->nama]))

@section('content')
    <x-back-link
        href="{{ route('operator.mata-pelajaran.index') }}"
        :label="__('operator.mata_pelajaran.detail_back')"
        class="min-h-11 min-w-11 px-2"
    />

    <x-page-header
        :title="$mataPelajaran->nama"
        :subtitle="__('operator.mata_pelajaran.detail_subtitle')"
    />

    @php
        $headers = [
            __('operator.mata_pelajaran.column_class'),
            __('operator.mata_pelajaran.column_teacher'),
            __('operator.mata_pelajaran.column_materials'),
        ];
    @endphp

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <x-table :headers="$headers">
                @forelse($kelasList as $kelas)
                    <x-tr>
                        <x-td>
                            <a href="{{ route('operator.kelas.materi', $kelas) }}" class="inline-flex min-h-11 min-w-11 items-center font-medium text-emerald-700 hover:underline dark:text-emerald-400">
                                {{ $kelas->nama_kelas }}
                            </a>
                        </x-td>
                        <x-td>{{ $kelas->guru?->name ?? '-' }}</x-td>
                        <x-td>{{ $kelas->materi_count }}</x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="3" class="py-6">
                            {{ __('operator.mata_pelajaran.empty_classes') }}
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
