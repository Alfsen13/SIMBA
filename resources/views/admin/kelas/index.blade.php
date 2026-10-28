@extends('layouts.app')

@section('title', __('operator.title_classes') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('navigation.classes')"
        :subtitle="__('operator.classes_subtitle')"
    >
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <x-btn-secondary href="{{ route('operator.import.template', ['jenis' => 'kelas']) }}" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Template Kelas (CSV/Excel)
                </x-btn-secondary>
                <form
                    action="{{ route('operator.import.kelas') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="flex items-center gap-2"
                >
                    @csrf
                    <input
                        type="file"
                        name="file"
                        accept=".csv,.txt,.xlsx,.xls"
                        required
                        class="text-xs text-slate-600 dark:text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60"
                    >
                    <x-btn-primary type="submit" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Import Kelas
                    </x-btn-primary>
                </form>
                <x-btn-primary href="{{ route('operator.kelas.create') }}" size="sm">
                    {{ __('operator.add_class') }}
                </x-btn-primary>
            </div>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    @if(session('import_errors'))
        <x-card class="mb-4 border-red-200 dark:border-red-800">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-700 dark:text-red-300">Ada error saat import kelas</p>
                    <ul class="mt-2 space-y-1 text-xs text-red-600 dark:text-red-400 list-disc pl-4">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </x-card>
    @endif

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30">
                        <x-th align="left">{{ __('operator.th_class_name') }}</x-th>
                        <x-th align="left">{{ __('operator.th_kategori') }}</x-th>
                        <x-th align="left">{{ __('operator.th_kode') }}</x-th>
                        <x-th align="left">{{ __('operator.th_teacher') }}</x-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($kelasList as $kelas)
                        <x-tr>
                            <x-td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-100 to-purple-100 flex items-center justify-center text-sm font-semibold text-emerald-700 dark:text-emerald-300 shrink-0">
                                        {{ substr($kelas->nama_kelas, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $kelas->nama_kelas }}</p>
                                        @if($kelas->deskripsi)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[200px]">{{ $kelas->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </x-td>
                            <x-td>
                                @if($kelas->kategori)
                                    <x-badge variant="info" :label="$kelas->kategori" />
                                @else
                                    <span class="text-slate-500 dark:text-slate-400 italic">—</span>
                                @endif
                            </x-td>
                            <x-td>
                                <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg text-slate-700 dark:text-slate-300">
                                    {{ $kelas->kode_kelas }}
                                </span>
                            </x-td>
                            <x-td>{!! $kelas->guru?->name ?? '<span class="text-slate-500 dark:text-slate-400 italic">' . __('operator.not_assigned') . '</span>' !!}</x-td>
                            <x-td align="right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-btn-secondary href="{{ route('operator.pengajuan-mengajar.show', $kelas) }}" size="sm">
                                        {{ __('operator.pengajuan_mengajar.lihat_pengajuan') }}
                                        @if($kelas->pengajuan_pending_count > 0)
                                            <span class="ml-0.5 inline-flex items-center justify-center w-4 h-4 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-300 text-[10px] font-semibold">
                                                {{ $kelas->pengajuan_pending_count }}
                                            </span>
                                        @endif
                                    </x-btn-secondary>
                                    @if($kelas->guru_id === null)
                                        <form
                                            action="{{ route('operator.kelas.destroy', $kelas) }}"
                                            method="POST"
                                            class="inline"
                                            x-data
                                            x-on:submit.prevent="confirm('{{ __('operator.konfirmasi_hapus_kelas') }}') && $el.submit()"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="{{ __('common.delete') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </x-td>
                        </x-tr>
                    @empty
                        <tr>
                            <x-td colspan="4" class="py-12 text-center">
                                <x-empty-state
                                    icon="building"
                                    :message="__('operator.empty_classes')"
                                />
                            </x-td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kelasList->hasPages())
            <div class="px-4 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $kelasList->appends(request()->query())->links() }}
            </div>
        @endif
    </x-card>
@endsection
