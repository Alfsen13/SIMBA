@extends('layouts.app')

@section('title', __('guru_soal.title_buat_tugas') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.pertemuan.show', $pertemuan) }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('guru_soal.daftar_tugas_baru')" />

    <div class="mb-4 text-sm text-slate-500 dark:text-slate-400">
        {{ __('guru_soal.kelas') }}: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $pertemuan->kelas->nama_kelas }}</span>
        &middot; {{ __('guru_soal.pertemuan') }}: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $pertemuan->nama_pertemuan }}</span>
    </div>

<form
        action="{{ route('pengajar.tugas.store') }}"
        method="POST"
        class="w-full"
        x-data="tugasBuilder({}, { pertemuanId: {{ $pertemuan->id }}, generateUrl: '{{ route('pengajar.tugas.generate') }}', bloomLevels: {{ Js::from($bloomLevels) }}, sumberKonteks: {{ Js::from($sumberKonteks) }} })"
    >
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $pertemuan->kelas_id }}">
        <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->id }}">

        <x-card class="mb-4 space-y-5">
            <x-form-input
                name="judul"
                :label="__('guru_soal.judul_tugas')"
                :value="old('judul')"
                required
            />
            <x-form-textarea
                name="deskripsi"
                :label="__('guru_soal.deskripsi_tugas')"
                :value="old('deskripsi')"
                rows="3"
            />
            <x-form-columns :cols="2">
                <div>
                    <label for="tipe" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        {{ __('guru_soal.tipe_tugas') }} <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <select
                        id="tipe"
                        name="tipe"
                        x-model="tipe"
                        required
                        class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"
                    >
                        <option value="upload">{{ __('guru_soal.opsi_upload') }}</option>
                        <option value="pilihan_ganda">{{ __('guru_soal.opsi_pilihan_ganda') }}</option>
                        <option value="essai">{{ __('guru_soal.opsi_esai_ai') }}</option>
                    </select>
                    <p x-show="tipe === 'essai'" x-cloak class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ __('guru_soal.help_esai_ai') }}
                    </p>
                </div>
                <x-form-input
                    name="batas_jam"
                    type="number"
                    min="1"
                    step="1"
                    :label="'Waktu Pengerjaan (Jam)'"
                    :value="old('batas_jam')"
                    placeholder="Contoh: 2"
                />
            </x-form-columns>
            <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2">Jika diisi, siswa wajib klik "Mulai Kerjakan" terlebih dahulu dan waktu dihitung mundur sesuai jumlah jam yang ditentukan.</p>
            <x-materi-utama-select />
        </x-card>

        <x-card x-show="tipe === 'pilihan_ganda' || tipe === 'essai'" x-cloak class="mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('guru_soal.generate_dengan_ai') }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                {{ __('guru_soal.ai_draf_pengantar') }}
                <span x-show="tipe === 'pilihan_ganda'">{{ __('guru_soal.pilihan_ganda_kecil') }}</span>
                <span x-show="tipe !== 'pilihan_ganda'">{{ __('guru_soal.esai_uraian_kecil') }}</span>
                {{ __('guru_soal.ai_draf_tugas_penjelas') }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                <div>
                    <label for="gen_jumlah" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_soal.jumlah_soal') }}</label>
                    <input
                        id="gen_jumlah"
                        type="number"
                        min="1"
                        max="12"
                        x-model.number="genJumlah"
                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </div>
                <div x-show="tipe === 'pilihan_ganda'">
                    <label for="gen_kesulitan" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_soal.tingkat_kesulitan') }}</label>
                    <select
                        id="gen_kesulitan"
                        x-model="genKesulitan"
                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                        <option value="mudah">{{ __('guru_soal.kesulitan_mudah') }}</option>
                        <option value="sedang">{{ __('guru_soal.kesulitan_sedang') }}</option>
                        <option value="sulit">{{ __('guru_soal.kesulitan_sulit') }}</option>
                    </select>
                </div>
                <div>
                    <label for="gen_topik" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_soal.topik_opsional') }}</label>
                    <input
                        id="gen_topik"
                        type="text"
                        x-model="genTopik"
                        placeholder="{{ __('guru_soal.topik_placeholder') }}"
                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </div>
            </div>
            <x-konteks-soal-picker :sumber-konteks="$sumberKonteks" />
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="generateSoal"
                    :disabled="generating"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg x-show="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg x-show="generating" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!generating">{{ __('guru_soal.generate_soal') }}</span>
                    <span x-show="generating" x-cloak>{{ __('guru_soal.menyusun') }}</span>
                </button>
                <span x-show="generating" x-cloak class="text-sm text-emerald-700 dark:text-emerald-400">
                    {{ __('guru_soal.ai_menyusun') }}
                </span>
            </div>
            <div class="mt-3">
                <p x-show="genMessage" x-cloak class="text-sm text-green-700 dark:text-green-400" x-text="genMessage"></p>
                <p x-show="genError" x-cloak class="text-sm text-red-600 dark:text-red-400" x-text="genError"></p>
            </div>
        </x-card>

        <x-card class="mb-4 space-y-5">
            <div x-show="tipe === 'essai'" x-cloak>
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('guru_soal.soal_esai') }}</label>
                    <x-btn-secondary type="button" @click="addSoal" size="sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('guru_soal.tambah_soal') }}
                    </x-btn-secondary>
                </div>
                <template x-for="(s, i) in soal" :key="i">
                    <div class="border border-slate-200 dark:border-slate-800 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('guru_soal.soal_prefix') }} <span x-text="i + 1"></span></span>
                            <button type="button" @click="removeSoal(i)" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs">{{ __('common.delete') }}</button>
                        </div>
                        <x-akurasi-soal soal="s" />
                        <textarea
                            x-model="s.pertanyaan"
                            rows="3"
                            placeholder="{{ __('guru_soal.ph_tulis_soal_esai') }}"
                            required
                            class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">{{ __('guru_soal.label_referensi') }}</label>
                        <textarea
                            x-model="s.referensi"
                            rows="2"
                            placeholder="{{ __('guru_soal.ph_referensi') }}"
                            class="block w-full rounded-lg border-slate-200 dark:border-slate-800 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">{{ __('guru_soal.label_bloom') }}</label>
                        <select
                            x-model="s.bloom"
                            class="block w-full rounded-lg border-slate-200 dark:border-slate-800 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">{{ __('guru_soal.bloom_tidak_ditentukan') }}</option>
                            <template x-for="l in bloomLevels" :key="l.code">
                                <option :value="l.code" x-text="l.code + ' — ' + l.name_id"></option>
                            </template>
                        </select>
                    </div>
                </template>
                <template x-if="soal.length === 0">
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.kosong_soal_esai') }}</p>
                </template>
            </div>

            <div x-show="tipe === 'pilihan_ganda'" x-cloak>
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('guru_soal.soal_pilihan_ganda') }}</label>
                    <x-btn-secondary type="button" @click="addQuestion" size="sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('guru_soal.tambah_soal') }}
                    </x-btn-secondary>
                </div>
                <template x-for="(q, i) in questions" :key="i">
                    <div class="border border-slate-200 dark:border-slate-800 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('guru_soal.soal_prefix') }} <span x-text="i + 1"></span></span>
                            <button type="button" @click="removeQuestion(i)" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs">{{ __('common.delete') }}</button>
                        </div>
                        <x-akurasi-soal soal="q" />
                        <textarea
                            x-model="q.pertanyaan"
                            rows="2"
                            placeholder="{{ __('guru_soal.ph_tulis_pertanyaan') }}"
                            required
                            class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                            <template x-for="(pil, j) in q.pilihan" :key="j">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        :name="'jawaban_' + i"
                                        :value="j"
                                        x-model="q.jawaban"
                                        class="shrink-0 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500"
                                    >
                                    <input
                                        type="text"
                                        x-model="q.pilihan[j]"
                                        :placeholder="'Pilihan ' + String.fromCharCode(65 + j)"
                                        required
                                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    >
                                    <span
                                        x-show="Number(q.jawaban) === Number(j)"
                                        x-cloak
                                        class="shrink-0 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                                    >{{ __('guru_soal.kunci') }}</span>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('guru_soal.pilih_radio') }}</p>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">{{ __('guru_soal.label_bloom') }}</label>
                        <select
                            x-model="q.bloom"
                            class="block w-full rounded-lg border-slate-200 dark:border-slate-800 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">{{ __('guru_soal.bloom_tidak_ditentukan') }}</option>
                            <template x-for="l in bloomLevels" :key="l.code">
                                <option :value="l.code" x-text="l.code + ' — ' + l.name_id"></option>
                            </template>
                        </select>
                    </div>
                </template>
                <template x-if="questions.length === 0">
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.kosong_soal_pg') }}</p>
                </template>
            </div>

            <input type="hidden" name="soal" :value="soalJson()">

            <x-btn-primary
                type="submit"
                class="w-full"
            >
                {{ __('common.save') }}
            </x-btn-primary>
        </x-card>
        <x-loading-overlay :judul="__('guru_soal.loading_tugas')" />
    </form>
@endsection