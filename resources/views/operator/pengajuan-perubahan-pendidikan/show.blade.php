@extends('layouts.app')

@section('title', 'Tinjau Pengajuan Perubahan Pendidikan')

@section('content')
    <x-back-link href="{{ route('operator.pengajuan-perubahan-pendidikan.index') }}" label="Kembali" />
    <x-page-header title="Tinjau Pengajuan Perubahan Pendidikan" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Profil Siswa</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Nama</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->user->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Email</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->user->email }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Jalur Saat Ini</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->user->profilPendidikan?->jalur?->nama ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Jenjang Saat Ini</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->user->profilPendidikan?->jenjang?->nama ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Tingkat Saat Ini</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->user->profilPendidikan?->tingkat?->nama ?? '-' }}</span></div>
            </div>
        </x-card>

        <x-card>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Pengajuan</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Jenis</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->jenis === 'kenaikan_kelas' ? 'Kenaikan Kelas' : 'Perpindahan Jenjang' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Jalur Tujuan</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->jalur?->nama }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Jenjang Tujuan</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->jenjang?->nama }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Tingkat Tujuan</span><span class="text-slate-900 dark:text-white">{{ $pengajuan->tingkat?->nama }}</span></div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Alasan Siswa</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->catatan_operator }}</p>
                </div>
            </div>
        </x-card>
    </div>

    @if($pengajuan->catatan_operator && in_array($pengajuan->status, ['approved', 'rejected', 'revision_required']))
        <x-card class="mt-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">Catatan Operator Terakhir</h3>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->catatan_operator }}</p>
            @if($pengajuan->reviewedBy)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Oleh {{ $pengajuan->reviewedBy->name }} • {{ $pengajuan->reviewed_at?->format('d/m/Y H:i') }}</p>
            @endif
        </x-card>
    @endif

    @if(in_array($pengajuan->status, ['pending', 'under_review', 'revision_required']))
        <x-card class="mt-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Tindakan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <form action="{{ route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan) }}" method="POST" class="space-y-2">
                    @csrf
                    <x-form-textarea
                        name="catatan_operator"
                        label="Catatan (opsional)"
                        placeholder="Catatan"
                        rows="3"
                    />
                    <x-btn-primary type="submit">Setujui & Update Profil</x-btn-primary>
                </form>
                <form action="{{ route('operator.pengajuan-perubahan-pendidikan.minta-revisi', $pengajuan) }}" method="POST" class="space-y-2">
                    @csrf
                    <x-form-textarea
                        name="catatan_operator"
                        label="Catatan revisi (wajib)"
                        placeholder="Catatan revisi"
                        rows="3"
                        required
                    />
                    <x-btn-primary type="submit">Minta Revisi</x-btn-primary>
                </form>
                <form action="{{ route('operator.pengajuan-perubahan-pendidikan.tolak', $pengajuan) }}" method="POST" class="space-y-2">
                    @csrf
                    <x-form-textarea
                        name="catatan_operator"
                        label="Alasan penolakan (wajib)"
                        placeholder="Alasan penolakan"
                        rows="3"
                        required
                    />
                    <x-btn-primary type="submit">Tolak</x-btn-primary>
                </form>
            </div>
        </x-card>
    @endif
@endsection
