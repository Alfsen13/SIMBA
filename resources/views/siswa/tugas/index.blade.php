@php $title = __('siswa.tugas.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('siswa.tugas.title')" />

    @if($tugasList->isEmpty())
        <x-empty-state
            :message="__('siswa.tugas.belum_ada')"
            icon='<svg class="w-6 h-6 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        />
    @else
        <x-card padding="false">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-left">
                    <tr>
                        <x-th align="left">{{ __('siswa.shared.judul') }}</x-th>
                        <x-th align="left">{{ __('navigation.classes') }}</x-th>
                        <x-th align="left">{{ __('siswa.shared.deadline_label') }}</x-th>
                        <x-th align="left">{{ __('common.status') }}</x-th>
                        <x-th align="left"></x-th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($tugasList as $t)
                        <x-tr>
                            <x-td class="font-medium text-slate-900 dark:text-slate-100">{{ $t->judul }}</x-td>
                            <x-td muted>{{ $t->kelas?->nama_kelas }}</x-td>
                            <x-td>
                                <span class="{{ $t->isLewatDeadline() && !$t->sudah_dikumpulkan ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                    {{ $t->deadline?->format('d M Y') ?: '-' }}
                                    @if($t->isLewatDeadline() && !$t->sudah_dikumpulkan)
                                        &larr; {{ __('siswa.tugas.lewat') }}
                                    @endif
                                </span>
                            </x-td>
                            <x-td>
                                @if($t->sudah_dikumpulkan)
                                    <x-badge variant="success">{{ __('siswa.shared.status_dikumpulkan') }}</x-badge>
                                @elseif($t->isLewatDeadline())
                                    <x-badge variant="danger">{{ __('siswa.shared.status_terlambat') }}</x-badge>
                                @else
                                    <x-badge variant="warning">{{ __('siswa.shared.status_belum') }}</x-badge>
                                @endif
                            </x-td>
                            <x-td>
                                <a
                                    href="{{ route('pelajar.tugas.show', $t) }}"
                                    class="text-emerald-700 dark:text-emerald-400 hover:underline"
                                >
                                    {{ __('common.detail') }}
                                </a>
                            </x-td>
                        </x-tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
        <div class="mt-4">{{ $tugasList->links() }}</div>
    @endif
</div>
@endsection
