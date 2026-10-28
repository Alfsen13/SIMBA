@extends('layouts.app')

@section('title', 'Mata Pelajaran - Operator')

@section('content')
    <x-page-header
        title="Mata Pelajaran"
        subtitle="Kelola mata pelajaran untuk kategori {{ auth()->user()->kategori_mapel }}"
    >
        <x-slot name="actions">
            <x-btn-primary :href="route('operator.mata-pelajaran.create')">
                Tambah Mata Pelajaran
            </x-btn-primary>
        </x-slot>
    </x-page-header>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <x-table :headers=[
                __('operator.mata_pelajaran.kode'),
                __('operator.mata_pelajaran.nama'),
                __('operator.mata_pelajaran.kategori'),
                __('common.actions'),
            ]>
                @forelse($mataPelajaran as $mapel)
                    <x-tr>
                        <x-td class="font-mono text-xs">{{ $mapel->kode }}</x-td>
                        <x-td>{{ $mapel->nama }}</x-td>
                        <x-td>
                            <x-badge variant="default">{{ $mapel->kategori }}</x-badge>
                        </x-td>
                        <x-td align="right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('operator.mata-pelajaran.edit', $mapel) }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline">
                                    {{ __('common.edit') }}
                                </a>
                                <form action="{{ route('operator.mata-pelajaran.destroy', $mapel) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-btn-secondary type="submit" color="red" size="sm">{{ __('common.delete') }}</x-btn-secondary>
                                </form>
                            </div>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="4" class="py-6">
                            <p class="text-sm">{{ __('operator.mata_pelajaran.belum_ada') }}</p>
                            <x-btn-primary :href="route('operator.mata-pelajaran.create')" class="mt-2 inline-flex items-center gap-1.5 text-xs">
                                {{ __('operator.mata_pelajaran.tambah_pertama') }}
                            </x-btn-primary>
                        </x-td>
                    </x-tr>
                @endforelse
            </x-table>
        </div>
        @if($mataPelajaran->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $mataPelajaran->links() }}
            </div>
        @endif
    </x-card>
@endsection
