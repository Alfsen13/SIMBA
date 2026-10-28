@extends('layouts.app')

@section('title', 'Pengajuan Perubahan Pendidikan - Operator')

@section('content')
    <x-page-header title="Pengajuan Perubahan Pendidikan" />

    <x-card class="mb-4">
        <form method="GET" class="flex items-center gap-2">
            <x-form-select
                name="status"
                label="Filter Status"
                placeholder="Semua Status"
                :options="[
                    '' => 'Semua Status',
                    'pending' => 'Menunggu',
                    'under_review' => 'Sedang Ditinjau',
                    'revision_required' => 'Perlu Revisi',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ]"
                :value="$status ?? ''"
            />
            <x-btn-primary type="submit" size="sm">Filter</x-btn-primary>
        </form>
    </x-card>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Profil Saat Ini</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Tujuan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pengajuanList as $p)
                        <tr>
                            <td class="px-6 py-3 text-slate-900 dark:text-slate-100">
                                <div class="font-medium">{{ $p->user->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $p->user->email }}</div>
                            </td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                {{ $p->jenis === 'kenaikan_kelas' ? 'Kenaikan Kelas' : 'Perpindahan Jenjang' }}
                            </td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                {{ $p->user->profilPendidikan?->jenjang?->nama }} - {{ $p->user->profilPendidikan?->tingkat?->nama }}
                            </td>
                            <td class="px-6 py-3 text-slate-900 dark:text-white">
                                {{ $p->jenjang?->nama }} - {{ $p->tingkat?->nama }}
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $map = [
                                        'pending' => ['label' => 'Menunggu', 'variant' => 'pending'],
                                        'under_review' => ['label' => 'Sedang Ditinjau', 'variant' => 'default'],
                                        'revision_required' => ['label' => 'Perlu Revisi', 'variant' => 'default'],
                                        'approved' => ['label' => 'Disetujui', 'variant' => 'verified'],
                                        'rejected' => ['label' => 'Ditolak', 'variant' => 'rejected'],
                                    ];
                                    $m = $map[$p->status] ?? ['label' => $p->status, 'variant' => 'default'];
                                @endphp
                                <x-badge :variant="$m['variant']" :label="$m['label']" />
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('operator.pengajuan-perubahan-pendidikan.show', $p) }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline">Tinjau</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                <p class="text-sm">Tidak ada pengajuan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $pengajuanList->links() }}
        </div>
    </x-card>
@endsection
