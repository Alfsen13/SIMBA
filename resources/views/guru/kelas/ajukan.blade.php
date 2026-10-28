@extends('layouts.app')

@section('title', __('guru_kelas.ajukan.title') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_kelas.ajukan.title')"
        :subtitle="__('guru_kelas.ajukan.subtitle', ['kelas' => $kelas->nama_kelas])"
    >
        <x-slot:actions>
            <x-btn-secondary href="{{ route('pengajar.kelas.index') }}" size="sm">
                {{ __('common.back') }}
            </x-btn-secondary>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2">
            <x-card>
                {{-- Penolakan sebelumnya ditampilkan sebagai bahan perbaikan ulang --}}
                @if($pengajuan && $pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_DITOLAK)
                    <div class="mb-4 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 px-4 py-3">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ __('guru_kelas.ajukan.ditolak_sebelumnya') }}</p>
                        @if($pengajuan->catatan_operator)
                            <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">{{ __('guru_kelas.ajukan.catatan_operator', ['catatan' => $pengajuan->catatan_operator]) }}</p>
                        @endif
                    </div>
                @endif

                <form action="{{ route('pengajar.kelas.ajukan.store', $kelas) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <x-form-textarea
                        name="rencana_ajar"
                        :label="__('guru_kelas.ajukan.rencana_label')"
                        :placeholder="__('guru_kelas.ajukan.rencana_placeholder')"
                        :value="old('rencana_ajar', $pengajuan?->status === \App\Models\PengajuanPengajarKelas::STATUS_DITOLAK ? $pengajuan->rencana_ajar : '')"
                        :rows="7"
                        :required="true"
                    />
                    <p class="-mt-2 mb-3 text-[11px] text-slate-500 dark:text-slate-400">{{ __('guru_kelas.ajukan.rencana_hint') }}</p>

                    <div class="mb-5">
                        <label for="berkas" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('guru_kelas.ajukan.berkas_label') }}
                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('guru_kelas.ajukan.berkas_hint') }})</span>
                        </label>
                        @if($pengajuan?->berkas_path)
                            <p class="mb-1.5 text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.ajukan.berkas_saat_ini', ['file' => basename($pengajuan->berkas_path)]) }}</p>
                        @endif
                        <input
                            type="file"
                            name="berkas"
                            id="berkas"
                            accept=".pdf,.doc,.docx"
                            class="block w-full text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 cursor-pointer"
                        >
                        @error('berkas')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <x-btn-primary type="submit" size="sm">
                            {{ __('guru_kelas.ajukan.kirim') }}
                        </x-btn-primary>
                        <x-btn-secondary href="{{ route('pengajar.kelas.index') }}" size="sm">
                            {{ __('common.cancel') }}
                        </x-btn-secondary>
                    </div>
                </form>
            </x-card>
        </div>

        <x-card>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('guru_kelas.ajukan.info_title') }}</h3>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.kelas.nama') }}</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $kelas->nama_kelas }}</dd>
                </div>
                @if($kelas->kategori)
                    <div>
                        <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.kelas.kategori') }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $kelas->kategori }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.kelas.pertemuan') }}</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $kelas->pertemuan_count ?? $kelas->pertemuan()->count() }} {{ __('guru_kelas.kata.pertemuan') }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">{{ __('guru_kelas.ajukan.info_alur') }}</p>
        </x-card>
    </div>
@endsection
