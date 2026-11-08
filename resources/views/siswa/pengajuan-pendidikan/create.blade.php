@extends('layouts.app')

@section('title', __('siswa.pengajuan.create_title').' - SIMBA')

@section('content')
    <x-back-link href="{{ route('pelajar.pengajuan-pendidikan.index') }}" :label="__('common.back')" class="min-h-11 min-w-11" />
    <x-page-header :title="__('siswa.pengajuan.create_title')" />

    <x-card class="max-w-xl">
        <form action="{{ route('pelajar.pengajuan-pendidikan.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-form-input
                name="jalur_saat_ini"
                type="text"
                :label="__('siswa.pengajuan.jalur_saat_ini')"
                :value="$jalur->nama"
                disabled
            />

            <x-form-select
                name="jenis"
                :label="__('siswa.pengajuan.jenis_pengajuan')"
                :options="[
                    'kenaikan_kelas' => __('siswa.pengajuan.kenaikan_kelas'),
                    'perpindahan_jenjang' => __('siswa.pengajuan.perpindahan_jenjang'),
                ]"
                :value="old('jenis')"
                required
            />
            @error('jenis')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <x-form-select
                name="jenjang_id"
                :label="__('siswa.pengajuan.jenjang_tujuan')"
                :options="$jenjangList->pluck('nama', 'id')->toArray()"
                :value="old('jenjang_id')"
                required
            />
            @if($jalur->kode === 'nonformal')
                <p class="mt-1.5 text-[11px] text-emerald-700 dark:text-emerald-400">{{ __('siswa.pengajuan.nonformal_hint') }}</p>
            @endif
            @error('jenjang_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <x-form-select
                name="tingkat_id"
                id="tingkat_id"
                :label="__('siswa.pengajuan.tingkat_tujuan')"
                :value="old('tingkat_id')"
                required
            >
                <option value="">{{ __('komponen.pilih_opsi') }}</option>
                @foreach($tingkatList as $t)
                    <option value="{{ $t->id }}" {{ old('tingkat_id') == $t->id ? 'selected' : '' }}>{{ $t->nama }}@if($jalur->kode === 'nonformal') @php $n=strtolower($t->nama); if(str_contains($n,'paket a')) echo ' ('.__('siswa.pengajuan.setara', ['jenjang' => 'SD/MI']).')'; elseif(str_contains($n,'paket b')) echo ' ('.__('siswa.pengajuan.setara', ['jenjang' => 'SMP/MTs']).')'; elseif(str_contains($n,'paket c')) echo ' ('.__('siswa.pengajuan.setara', ['jenjang' => 'SMA/SMK/MA']).')'; @endphp @endif</option>
                @endforeach
            </x-form-select>
            @error('tingkat_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <x-form-textarea
                name="alasan"
                :label="__('siswa.pengajuan.alasan_pengajuan')"
                :value="old('alasan')"
                rows="4"
                required
            />
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.alasan_hint') }}</p>
            @error('alasan')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="flex justify-end">
                <x-btn-primary type="submit">
                    {{ __('siswa.pengajuan.kirim_pengajuan') }}
                </x-btn-primary>
            </div>
        </form>
    </x-card>
@endsection
