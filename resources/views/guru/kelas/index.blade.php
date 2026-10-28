@extends('layouts.app')

@section('title', __('guru_kelas.kelas.title') . ' - SIMBA')

@section('content')
    <x-page-header :title="__('guru_kelas.kelas.saya')" />

    {{-- Kelas yang sudah diambil --}}
    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('guru_kelas.kelas.saya') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
        @forelse($kelasSaya as $kelas)
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $kelas->kode_kelas }}</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $kelas->nama_kelas }}</h3>
                @if($kelas->kategori)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('guru_kelas.kategori_label', ['kategori' => $kelas->kategori]) }}</p>
                @endif
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $kelas->siswa_count }} {{ __('guru_kelas.kata.siswa') }} &middot; {{ $kelas->pertemuan_count }} {{ __('guru_kelas.kata.pertemuan') }}</p>
                <div class="mt-4 flex gap-2">
                    <x-btn-secondary href="{{ route('pengajar.kelas.show', $kelas) }}" size="sm">
                        {{ __('common.detail') }}
                    </x-btn-secondary>
                    <x-btn-secondary href="{{ route('pengajar.kelas.edit', $kelas) }}" size="sm">
                        {{ __('guru_kelas.edit') }}
                    </x-btn-secondary>
                    <form
                        action="{{ route('pengajar.kelas.destroy', $kelas) }}"
                        method="POST"
                        class="inline"
                        x-data
                        x-on:submit.prevent="confirm('{{ __('guru_kelas.kelas.konfirmasi_lepaskan') }}') && $el.submit()"
                    >
                        @csrf
                        @method('DELETE')
                        <x-btn-secondary type="submit" color="red" size="sm">
                            {{ __('guru_kelas.kelas.lepaskan') }}
                        </x-btn-secondary>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state
                    :message="__('guru_kelas.kelas.belum_ambil')"
                />
            </div>
        @endforelse
    </div>

    @if($kelasSaya->hasPages())
        <div class="mb-4">{{ $kelasSaya->links() }}</div>
    @endif

    {{-- Kelas tersedia untuk diajukan (dengan bahan rencana ajar) --}}
    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('guru_kelas.kelas.tersedia') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($kelasTersedia as $kelas)
            @php $pengajuan = $pengajuanSaya->get($kelas->id); @endphp
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    @if($pengajuan && $pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING)
                        <x-badge variant="pending" :label="__('guru_kelas.ajukan.badge_pending')" />
                    @elseif($pengajuan && $pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_DITOLAK)
                        <x-badge variant="rejected" :label="__('guru_kelas.ajukan.badge_ditolak')" />
                    @else
                        <x-badge variant="verified">{{ __('guru_kelas.kelas.tersedia_badge') }}</x-badge>
                    @endif
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $kelas->kode_kelas }}</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $kelas->nama_kelas }}</h3>
                @if($kelas->kategori)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('guru_kelas.kategori_label', ['kategori' => $kelas->kategori]) }}</p>
                @endif
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $kelas->siswa_count }} {{ __('guru_kelas.kata.siswa') }} &middot; {{ $kelas->pertemuan_count }} {{ __('guru_kelas.kata.pertemuan') }}</p>
                @if($pengajuan && $pengajuan->catatan_operator && $pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_DITOLAK)
                    <p class="mt-2 text-xs text-amber-700 dark:text-amber-400">{{ __('guru_kelas.ajukan.catatan_operator', ['catatan' => $pengajuan->catatan_operator]) }}</p>
                @endif
                <div class="mt-4">
                    @if($pengajuan && $pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING)
                        <p class="w-full text-sm text-center py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                            {{ __('guru_kelas.ajukan.menunggu_operator') }}
                        </p>
                    @else
                        <x-btn-primary href="{{ route('pengajar.kelas.ajukan', $kelas) }}" size="sm" class="w-full justify-center">
                            {{ $pengajuan ? __('guru_kelas.ajukan.ajukan_ulang') : __('guru_kelas.ajukan.ajukan') }}
                        </x-btn-primary>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state :message="__('guru_kelas.kelas.tidak_tersedia')" />
            </div>
        @endforelse
    </div>

    @if($kelasTersedia->hasPages())
        <div class="mt-6">{{ $kelasTersedia->links() }}</div>
    @endif
@endsection
