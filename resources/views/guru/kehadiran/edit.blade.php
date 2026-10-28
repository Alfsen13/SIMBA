@extends('layouts.app')

@section('title', __('guru_kelas.kehadiran.title') . ' - ' . $pertemuan->nama_pertemuan . ' - SIMBA')

@php
    $labelStatus = [
        'hadir' => ucfirst(__('guru_kelas.kehadiran.hadir')),
        'izin' => ucfirst(__('guru_kelas.kehadiran.izin')),
        'sakit' => ucfirst(__('guru_kelas.kehadiran.sakit')),
        'alpha' => ucfirst(__('guru_kelas.kehadiran.alpha')),
    ];
    $nilaiAwal = $siswa->mapWithKeys(fn ($s) => [
        $s->id => old('status.' . $s->id, optional($kehadiran->get($s->id))->status ?? 'hadir'),
    ]);
@endphp

@section('content')
    <x-back-link
        href="{{ route('pengajar.pertemuan.show', $pertemuan) }}"
        :label="__('guru_kelas.kehadiran.kembali')"
    />

    <x-page-header
        :title="__('guru_kelas.kehadiran.dengan_nama', ['nama' => $pertemuan->nama_pertemuan])"
        :subtitle="__('guru_kelas.kelas_label', ['nama' => $pertemuan->kelas->nama_kelas])"
    />

    <x-card x-data="kehadiranForm()">
        <form
            action="{{ route('pengajar.kehadiran.update', $pertemuan) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $siswa->count() }} {{ __('guru_kelas.kehadiran.siswa_terdaftar') }}</p>
                <div class="flex gap-2">
                    <x-btn-secondary type="button" size="sm" @click="tandaiSemua('hadir')">
                        {{ __('guru_kelas.kehadiran.tandai_hadir') }}
                    </x-btn-secondary>
                    <x-btn-secondary type="button" size="sm" color="red" @click="tandaiSemua('alpha')">
                        {{ __('guru_kelas.kehadiran.tandai_alpha') }}
                    </x-btn-secondary>
                </div>
            </div>

            @forelse($siswa as $s)
                @php($h = $kehadiran->get($s->id))
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $s->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $s->email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select
                            name="status[{{ $s->id }}]"
                            x-model="status[{{ $s->id }}]"
                            class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none"
                        >
                            @foreach($labelStatus as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input
                            type="text"
                            name="catatan[{{ $s->id }}]"
                            value="{{ old('catatan.' . $s->id, $h->catatan ?? '') }}"
                            placeholder="{{ __('guru_kelas.catatan') }}"
                            class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none w-40"
                        >
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.kehadiran.belum_ada_siswa') }}</p>
            @endforelse

            @if($siswa->isNotEmpty())
                <x-btn-primary type="submit" class="mt-5 w-full">
                    {{ __('guru_kelas.kehadiran.simpan') }}
                </x-btn-primary>
            @endif
        </form>
    </x-card>
@endsection

@push('scripts')
    <script>
        function kehadiranForm() {
            return {
                status: @json($nilaiAwal),
                tandaiSemua(nilai) {
                    Object.keys(this.status).forEach((id) => {
                        this.status[id] = nilai;
                    });
                },
            };
        }
    </script>
@endpush
