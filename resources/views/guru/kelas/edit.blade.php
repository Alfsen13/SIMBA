@extends('layouts.app')

@section('title', __('guru_kelas.kelas.edit_title') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.kelas.index') }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('guru_kelas.kelas.edit_title')" />

    <x-card>
        <form
            action="{{ route('pengajar.kelas.update', $kelas) }}"
            method="POST"
        >
            @csrf
            @method('PUT')
            <x-form-columns :cols="2">
                <x-form-input
                    name="nama_kelas"
                    :label="__('guru_kelas.kelas.nama')"
                    :value="old('nama_kelas', $kelas->nama_kelas)"
                    required
                />
                <x-form-input
                    name="kategori"
                    :label="__('guru_kelas.kelas.kategori')"
                    :value="old('kategori', $kelas->kategori)"
                    :placeholder="__('guru_kelas.kelas.kategori_placeholder')"
                />
            </x-form-columns>
            <x-form-select
                name="aturan_kelayakan"
                :label="__('common.label_aturan_kelayakan')"
                :options="[
                    'sesuai_profil' => __('common.aturan_sesuai_profil'),
                    'sesuai_jenjang' => __('common.aturan_sesuai_jenjang'),
                    'terbuka' => __('common.aturan_terbuka'),
                    'khusus' => __('common.aturan_khusus'),
                ]"
                :value="old('aturan_kelayakan', $kelas->aturan_kelayakan)"
            />
            <x-form-textarea
                name="deskripsi"
                :label="__('guru_kelas.kelas.deskripsi')"
                :value="old('deskripsi', $kelas->deskripsi)"
                rows="3"
            />
            <x-btn-primary type="submit" class="w-full">
                {{ __('guru_kelas.update') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
