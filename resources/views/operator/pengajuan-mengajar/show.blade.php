@extends('layouts.app')

@section('title', __('operator.pengajuan_mengajar.page_title'))

@section('content')
    <div class="py-6">
        <x-page-header
            :title="__('operator.pengajuan_mengajar.title')"
            :subtitle="__('operator.pengajuan_mengajar.subtitle', ['kelas' => $kelas->nama_kelas])"
        >
            <x-slot name="actions">
                <x-btn-secondary href="{{ route('operator.kelas.index') }}" size="sm" class="min-h-11">
                    {{ __('common.back') }}
                </x-btn-secondary>
            </x-slot>
        </x-page-header>

        {{-- Ringkasan kelas yang ditinjau --}}
        <x-card class="mb-6">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ __('navigation.classes') }}</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $kelas->nama_kelas }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ __('operator.th_kode') }}</span>
                    <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg text-slate-700 dark:text-slate-300">{{ $kelas->kode_kelas }}</span>
                </div>
                @if($kelas->kategori)
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ __('operator.th_kategori') }}</span>
                        <x-badge variant="info" :label="$kelas->kategori" />
                    </div>
                @endif
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ __('operator.th_teacher') }}</span>
                    @if($kelas->guru)
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $kelas->guru->name }}</span>
                    @else
                        <span class="text-slate-500 dark:text-slate-400 italic">{{ __('operator.not_assigned') }}</span>
                    @endif
                </div>
            </div>
            @if($kelas->guru)
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_mengajar.kelas_sudah_diampu_info') }}</p>
            @endif
        </x-card>

        @if($pengajuanList->isEmpty())
            <x-empty-state :message="__('operator.pengajuan_mengajar.kosong')" />
        @else
            <x-card :padding="false">
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($pengajuanList as $pengajuan)
                        <div class="p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $pengajuan->pengajar->name }}</span>
                                        <x-badge
                                            :variant="$pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING ? 'pending' : ($pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_DISETUJUI ? 'verified' : 'rejected')"
                                            :label="__('operator.pengajuan_mengajar.badge_' . $pengajuan->status)"
                                        />
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $pengajuan->pengajar->email }}
                                        &middot; {{ __('operator.pengajuan_mengajar.diajukan_pada', ['waktu' => $pengajuan->created_at->format('d/m/Y H:i')]) }}
                                    </p>
                                </div>

                                @if($pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING && $kelas->guru_id === null)
                                    <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center">
                                        <form method="POST" action="{{ route('operator.pengajuan-mengajar.setujui', $pengajuan) }}">
                                            @csrf
                                            <x-btn-primary type="submit" size="sm" class="min-h-11 w-full sm:w-auto">{{ __('operator.approve') }}</x-btn-primary>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('operator.pengajuan-mengajar.tolak', $pengajuan) }}"
                                            class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center"
                                            x-data
                                            x-on:submit.prevent="confirm('{{ __('operator.pengajuan_mengajar.konfirmasi_tolak') }}') && $el.submit()"
                                        >
                                            @csrf
                                            <label for="catatan-penolakan-{{ $pengajuan->id }}" class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                                {{ __('operator.pengajuan_mengajar.catatan_label') }}
                                            </label>
                                            <input
                                                type="text"
                                                name="catatan"
                                                id="catatan-penolakan-{{ $pengajuan->id }}"
                                                maxlength="1000"
                                                placeholder="{{ __('operator.pengajuan_mengajar.catatan_placeholder') }}"
                                                class="w-full min-w-0 px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none sm:w-52"
                                            >
                                            <x-btn-secondary type="submit" size="sm" color="red" class="min-h-11 w-full sm:w-auto">{{ __('operator.title_reject') }}</x-btn-secondary>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('operator.pengajuan_mengajar.rencana_label') }}</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $pengajuan->rencana_ajar }}</p>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                @if($pengajuan->berkas_path)
                                    <a
                                        href="{{ route('operator.pengajuan-mengajar.berkas', $pengajuan) }}"
                                        class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium hover:underline"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        {{ __('operator.pengajuan_mengajar.unduh_berkas') }}
                                    </a>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400 italic">{{ __('operator.pengajuan_mengajar.tanpa_berkas') }}</span>
                                @endif
                                @if($pengajuan->reviewed_at)
                                    <span class="text-slate-500 dark:text-slate-400">
                                        {{ __('operator.pengajuan_mengajar.diproses_pada', ['waktu' => $pengajuan->reviewed_at->format('d/m/Y H:i')]) }}
                                    </span>
                                @endif
                            </div>

                            @if($pengajuan->catatan_operator)
                                <p class="mt-2 text-xs text-amber-700 dark:text-amber-400">
                                    {{ __('operator.pengajuan_mengajar.catatan_diberikan', ['catatan' => $pengajuan->catatan_operator]) }}
                                </p>
                            @endif

                            {{-- Profil pelamar dari pendaftaran (aplikasi_pengajar): bahan verifikasi tambahan --}}
                            @php $aplikasi = $pengajuan->pengajar->aplikasiPengajar; @endphp
                            <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 p-3">
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">{{ __('operator.pengajuan_mengajar.profil_title') }}</p>
                                @if($aplikasi)
                                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm">
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5">{{ __('operator.pengajuan_mengajar.profil_pendidikan') }}</dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $aplikasi->pendidikan_tertinggi ?? '—' }}</dd>
                                        </div>
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5">{{ __('operator.pengajuan_mengajar.profil_pengalaman') }}</dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $aplikasi->pengalaman_mengajar ?? '—' }}</dd>
                                        </div>
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5">{{ __('operator.pengajuan_mengajar.profil_kategori_mapel') }}</dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $aplikasi->kategori_mapel ?? '—' }}</dd>
                                        </div>
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5">{{ __('operator.pengajuan_mengajar.profil_jenjang') }}</dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $aplikasi->jenjang_dan_tingkat ?? '—' }}</dd>
                                        </div>
                                    </dl>

                                    @if($aplikasi->mata_pelajaran)
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_mengajar.profil_mapel_klaim') }}</span>
                                            @foreach($aplikasi->mata_pelajaran as $mapel)
                                                <x-badge variant="default" :label="$mapel" />
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                        @if($aplikasi->cv_path)
                                            <a href="{{ route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'cv']) }}" class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                {{ __('operator.pengajuan_mengajar.unduh_cv') }}
                                            </a>
                                        @endif
                                        @if($aplikasi->ijazah_path)
                                            <a href="{{ route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'ijazah']) }}" class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                {{ __('operator.pengajuan_mengajar.unduh_ijazah') }}
                                            </a>
                                        @endif
                                        @if(!$aplikasi->cv_path && !$aplikasi->ijazah_path)
                                            <span class="text-slate-500 dark:text-slate-400 italic">{{ __('operator.pengajuan_mengajar.profil_tanpa_berkas') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">{{ __('operator.pengajuan_mengajar.profil_kosong') }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>
@endsection
