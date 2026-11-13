@extends('layouts.app')

@section('title', __('guru_soal.title_buat_kuis') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.pertemuan.show', $pertemuan) }}"
        :label="__('common.back')"
    />

    <x-page-header
        :title="__('guru_soal.daftar_kuis_baru')"
        subtitle="{{ __('guru_soal.kelas') }}: {{ $pertemuan->kelas->nama_kelas }} &middot; {{ __('guru_soal.pertemuan') }}: {{ $pertemuan->nama_pertemuan }}"
    />

    <form
        action="{{ route('pengajar.kuis.store') }}"
        method="POST"
        class="w-full"
        x-data="kuisBuilder([], { pertemuanId: {{ $pertemuan->id }}, generateUrl: '{{ route('pengajar.kuis.generate') }}', chunkUrl: '{{ route('pengajar.konteks-chunk') }}', bloomLevels: {{ Js::from($bloomLevels) }}, sumberKonteks: {{ Js::from($sumberKonteks) }} })"
    >
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $pertemuan->kelas_id }}">
        <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->id }}">

        <x-card class="mb-4 space-y-5">
            <x-form-input
                name="judul"
                :label="__('guru_soal.judul_kuis')"
                value="{{ old('judul') }}"
                required
            />
            <x-form-columns :cols="2">
                <x-form-input
                    name="durasi_menit"
                    type="number"
                    :label="__('guru_soal.durasi_menit_label')"
                    value="{{ old('durasi_menit', 30) }}"
                    required
                />
            </x-form-columns>
            <x-materi-utama-select />
        </x-card>

        <x-card class="mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('guru_soal.generate_dengan_ai') }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                {{ __('guru_soal.ai_draf_kuis') }}
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
                <div>
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
                    <span x-show="!generating">{{ __('guru_soal.generate') }}</span>
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

        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_soal.soal') }}</h2>
                <x-btn-primary
                    type="button"
                    size="sm"
                    @click="addQuestion"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    {{ __('guru_soal.tambah_soal_manual') }}
                </x-btn-primary>
            </div>

            <template x-for="(q, i) in questions" :key="i">
                <div class="border border-slate-200 dark:border-slate-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('guru_soal.soal_prefix') }} <span x-text="i + 1"></span></span>
                        <button
                            type="button"
                            @click="removeQuestion(i)"
                            class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm flex items-center gap-1"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            {{ __('common.delete') }}
                        </button>
                    </div>

                    <x-akurasi-soal soal="q" />

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            {{ __('guru_soal.pertanyaan') }}
                        </label>
                        <input
                            type="text"
                            x-model="q.pertanyaan"
                            placeholder="{{ __('guru_soal.ph_masukkan_pertanyaan') }}"
                            class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                        <x-pratinjau-rumus value="q.pertanyaan" />
                        <x-rumus-bantu value="q.pertanyaan" />
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            {{ __('guru_soal.label_bloom') }}
                        </label>
                        <select
                            x-model="q.bloom"
                            class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">{{ __('guru_soal.bloom_tidak_ditentukan') }}</option>
                            <template x-for="l in bloomLevels" :key="l.code">
                                <option :value="l.code" x-text="l.code + ' — ' + l.name_id"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <template x-for="(pil, j) in q.pilihan" :key="j">
                            <div>
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
                                    class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                                {{-- Penanda kunci jawaban hasil AI, supaya guru bisa langsung melihat mana
                                     yang tercentang di antara 4 opsi. --}}
                                <span
                                    x-show="Number(q.jawaban) === Number(j)"
                                    x-cloak
                                    class="shrink-0 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                                >{{ __('guru_soal.kunci') }}</span>
                              </div>
                              <x-pratinjau-rumus value="q.pilihan[j]" />
                              <x-rumus-bantu value="q.pilihan[j]" />
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('guru_soal.pilih_radio') }}
                    </p>
                </div>
            </template>

            <template x-if="questions.length === 0">
                <x-empty-state
                    :message="__('guru_soal.kosong_soal_kuis')"
                />
            </template>

            <input
                type="hidden"
                name="soal"
                x-bind:value="JSON.stringify(questions)"
            >
            <x-btn-primary
                type="submit"
                class="w-full mt-4"
            >
                {{ __('guru_soal.simpan_kuis') }}
            </x-btn-primary>
        </x-card>
        <x-loading-overlay :judul="__('guru_soal.loading_kuis')" />
    </form>
@endsection