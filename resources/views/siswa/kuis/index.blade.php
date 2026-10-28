@php $title = __('siswa.kuis.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('siswa.kuis.title')" />

    @if($kuisList->isEmpty())
        <x-empty-state
            :message="__('siswa.kuis.belum_ada')"
            icon='<svg class="w-6 h-6 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    @else
        <x-card padding="false">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-left">
                    <tr>
                        <x-th align="left">{{ __('siswa.shared.judul') }}</x-th>
                        <x-th align="left">{{ __('navigation.classes') }}</x-th>
                        <x-th align="left">{{ __('siswa.shared.durasi') }}</x-th>
                        <x-th align="left">{{ __('common.status') }}</x-th>
                        <x-th align="left"></x-th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($kuisList as $k)
                        <x-tr>
                            <x-td class="font-medium text-slate-900 dark:text-slate-100">{{ $k->judul }}</x-td>
                            <x-td muted>{{ $k->kelas?->nama_kelas }}</x-td>
                            <x-td>{{ $k->durasi_menit }} {{ __('siswa.shared.menit') }}</x-td>
                            <x-td>
                                @if($k->sudah_dikerjakan)
                                    <x-badge variant="selesai">{{ __('siswa.shared.status_selesai') }}</x-badge>
                                @else
                                    <x-badge variant="warning">{{ __('siswa.shared.status_belum') }}</x-badge>
                                @endif
                            </x-td>
                            <x-td>
                                @if($k->sudah_dikerjakan)
                                    <a
                                        href="{{ route('pelajar.kuis.hasil', $k) }}"
                                        class="text-emerald-700 dark:text-emerald-400 hover:underline"
                                    >
                                        {{ __('siswa.kuis.lihat_hasil') }}
                                    </a>
                                @else
                                    <a
                                        href="{{ route('pelajar.kuis.show', $k) }}"
                                        class="text-emerald-700 dark:text-emerald-400 hover:underline"
                                    >
                                        {{ __('siswa.kuis.kerjakan') }}
                                    </a>
                                @endif
                            </x-td>
                        </x-tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
        <div class="mt-4">{{ $kuisList->links() }}</div>
    @endif
</div>
@endsection
