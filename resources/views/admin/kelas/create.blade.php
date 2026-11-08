@extends('layouts.app')

@section('title', __('operator.title_class_create') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('operator.kelas.index') }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('operator.page_class_create')" />

    <x-card class="max-w-xl">
        <form
            action="{{ route('operator.kelas.store') }}"
            method="POST"
            x-data="{
                isiKategoriDariMapel(pilih) {
                    const kat = pilih.selectedOptions[0]?.dataset?.kategori || '';
                    if (kat) document.getElementById('kategori').value = kat;
                }
            }"
        >
            @csrf
            <x-form-input
                name="nama_kelas"
                :label="__('operator.th_class_name')"
                :value="old('nama_kelas')"
                required
            />

            <div class="mb-3">
                <label for="mata_pelajaran_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    {{ __('operator.th_mata_pelajaran') }}
                    <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('common.optional') }})</span>
                </label>
                <select
                    name="mata_pelajaran_id"
                    id="mata_pelajaran_id"
                    @change="isiKategoriDariMapel($event.target)"
                    class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('mata_pelajaran_id') border-red-400 @enderror"
                >
                    <option value="">{{ __('komponen.pilih_opsi') }}</option>
                    @foreach ($mapelGrup as $grup)
                        <optgroup label="{{ $grup['kategori'] !== '' ? $grup['kategori'] : __('operator.mapel_tanpa_kategori') }}">
                            @foreach ($grup['items'] as $mapel)
                                <option
                                    value="{{ $mapel->id }}"
                                    data-kategori="{{ $mapel->kategori }}"
                                    {{ old('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}
                                >{{ $mapel->nama }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">{{ __('operator.mapel_operator_hint') }}</p>
                @error('mata_pelajaran_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <input type="hidden" name="kategori" id="kategori" value="{{ old('kategori') }}">
            @error('kategori')
                <p role="alert" class="mb-3 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="mb-3">
                <label for="jalur_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    {{ __('operator.label_jalur') }}
                    <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('common.optional') }})</span>
                </label>
                <select
                    name="jalur_id"
                    id="jalur_id"
                    class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('jalur_id') border-red-400 @enderror"
                >
                    <option value="">{{ __('komponen.pilih_opsi') }}</option>
                    @foreach ($jalurList as $j)
                        <option value="{{ $j->id }}" {{ old('jalur_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                    @endforeach
                </select>
                @error('jalur_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="jenjang_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    {{ __('operator.label_jenjang') }} <span class="text-red-500">*</span>
                </label>
                <select
                    name="jenjang_id"
                    id="jenjang_id"
                    required
                    class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('jenjang_id') border-red-400 @enderror"
                >
                    <option value="">{{ __('komponen.pilih_opsi') }}</option>
                    @foreach ($jenjangList as $jj)
                        <option value="{{ $jj->id }}" {{ old('jenjang_id') == $jj->id ? 'selected' : '' }}>{{ $jj->nama }}</option>
                    @endforeach
                </select>
                @error('jenjang_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="tingkat_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    {{ __('operator.label_tingkat') }} <span class="text-red-500">*</span>
                </label>
                <select
                    name="tingkat_id"
                    id="tingkat_id"
                    required
                    class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('tingkat_id') border-red-400 @enderror"
                >
                    <option value="">{{ __('komponen.pilih_opsi') }}</option>
                    @foreach ($tingkatGrup as $grupT)
                        <optgroup label="{{ $grupT['nama'] }}">
                            @foreach ($grupT['items'] as $t)
                                <option value="{{ $t->id }}" {{ old('tingkat_id') == $t->id ? 'selected' : '' }}>{{ $t->nama }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('tingkat_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <x-form-input
                type="number"
                name="kapasitas"
                :label="__('operator.label_kapasitas')"
                :value="old('kapasitas')"
                :placeholder="__('operator.kapasitas_placeholder')"
            />

            <x-form-input
                name="periode"
                :label="__('operator.label_periode')"
                :value="old('periode')"
                :placeholder="__('operator.periode_placeholder')"
            />

            <input type="hidden" name="status" value="dibuka">
            <input type="hidden" name="aturan_kelayakan" value="sesuai_jenjang">

            <x-form-textarea
                name="deskripsi"
                :label="__('operator.field_description')"
                :value="old('deskripsi')"
                rows="3"
            />
            <x-btn-primary type="submit" class="w-full">
                {{ __('common.save') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
