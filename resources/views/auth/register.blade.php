@extends('layouts.guest')

@section('title', __('auth.register_button') . ' - SIMBA')

@section('container_width', 'max-w-2xl')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-7">
        <div class="text-center mb-5">
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('auth.register_title') }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ __('auth.register_hint') }}
            </p>
        </div>

        <script type="application/json" id="reg-master">@json($regMaster)</script>
        <script type="application/json" id="mapel-by-kategori">@json($mapelByKategori)</script>

        <form
            action="{{ route('register') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-4"
            x-data="{
                role: '{{ in_array(old('role'), ['pelajar', 'pengajar'], true) ? old('role') : 'pelajar' }}',
                jalurId: '{{ preg_replace('/[^0-9]/', '', (string) old('jalur_id')) }}',
                jenjangId: '{{ preg_replace('/[^0-9]/', '', (string) old('jenjang_id')) }}',
                tingkatId: '{{ preg_replace('/[^0-9]/', '', (string) old('tingkat_id')) }}',
                mapelInput: '',
                mapel: @json(old('mata_pelajaran', [])),
                ...JSON.parse(document.getElementById('reg-master').textContent),
                get jenjangList() { return this.jenjang[this.jalurId] || [] },
                get tingkatList() { return this.tingkat[this.jenjangId] || [] },
                get jalurKode() {
                    const j = this.jalur.find(x => String(x.id) === String(this.jalurId));
                    return j ? j.kode : null;
                },
                get butuhInstitusi() { return this.jalurId !== '' && this.jalurKode !== 'self_learning' },
                tambahMapel(nama = null) {
                    const t = (nama || this.mapelInput).trim();
                    if (t && !this.mapel.some(m => m.toLowerCase() === t.toLowerCase())) {
                        this.mapel.push(t);
                    }
                    this.mapelInput = '';
                },
                tambahDariPreview(nama) {
                    this.tambahMapel(nama);
                }
            }"
        >
            @csrf

            <div class="grid sm:grid-cols-2 gap-x-4">
                <x-form-input
                    name="name"
                    :label="__('auth.name')"
                    value="{{ old('name') }}"
                    placeholder="{{ __('auth.name_placeholder') }}"
                    required
                    icon='<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
                />

                <x-form-input
                    name="email"
                    type="email"
                    label="Email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    required
                    icon='<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                />
            </div>

            <div>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">
                    {{ __('auth.role') }}
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <label
                        @click="role = 'pelajar'; if (jalurKode !== 'nonformal') { jalurId = ''; jenjangId = ''; tingkatId = ''; }"
                        :class="role === 'pelajar' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'"
                        class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors focus-within:ring-2 focus-within:ring-emerald-500/40"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="pelajar"
                            x-model="role"
                            class="sr-only"
                        >
                        <div
                            :class="role === 'pelajar' ? 'bg-emerald-700' : 'bg-slate-100 dark:bg-slate-800'"
                            class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                        >
                            <svg
                                :class="role === 'pelajar' ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('auth.role_pelajar') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ __('auth.role_hint_pelajar') }}</div>
                        </div>
                    </label>

                    <label
                        @click="role = 'pengajar'"
                        :class="role === 'pengajar' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'"
                        class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors focus-within:ring-2 focus-within:ring-emerald-500/40"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="pengajar"
                            x-model="role"
                            class="sr-only"
                        >
                        <div
                            :class="role === 'pengajar' ? 'bg-slate-800 dark:bg-slate-600' : 'bg-slate-100 dark:bg-slate-800'"
                            class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                        >
                            <svg
                                :class="role === 'pengajar' ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('auth.role_pengajar') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ __('auth.role_hint_pengajar') }}</div>
                        </div>
                    </label>
                </div>

                @error('role')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1" role="alert">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div
                x-show="role === 'pengajar'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                <x-form-input
                    name="nip"
                    label="NIP ({{ __('auth.opsional') }})"
                    value="{{ old('nip') }}"
                    placeholder="{{ __('auth.nip_placeholder') }}"
                    icon='<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>'
                />
            </div>

            {{-- Data aplikasi calon pengajar: x-if (bukan x-show) supaya field-nya
                 tidak ikut terkirim saat role pelajar dipilih. --}}
            <template x-if="role === 'pengajar'">
                <x-form-section
                    :title="__('auth.aplikasi_pengajar')"
                    :hint="__('auth.aplikasi_pengajar_hint')"
                >
                    <x-form-input
                        name="pendidikan_tertinggi"
                        :label="__('auth.pendidikan_tertinggi_label')"
                        value="{{ old('pendidikan_tertinggi') }}"
                        placeholder="{{ __('auth.pendidikan_tertinggi_placeholder') }}"
                    />

                    <x-form-textarea
                        name="pengalaman_mengajar"
                        :label="__('auth.pengalaman_mengajar_label')"
                        value="{{ old('pengalaman_mengajar') }}"
                        placeholder="{{ __('auth.pengalaman_mengajar_placeholder') }}"
                        :rows="3"
                    />

                    <div class="mb-3" x-data="mapelPreview">
                        <label for="kategori_mapel" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.kategori_mapel_label') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="kategori_mapel"
                            id="kategori_mapel"
                            x-model="kategori"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('kategori_mapel') border-red-400 @enderror"
                            required
                        >
                            <option value="">{{ __('komponen.pilih_opsi') }}</option>
                            @foreach ($kategoriMapelList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori_mapel') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                            {{ __('auth.kategori_mapel_hint') }}
                        </p>
                        <div class="mt-2" x-show="kategori && mapelByKategori[kategori]?.length > 0" x-transition.opacity>
                            <p class="text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                {{ __('auth.kategori_mapel_preview_label') }}
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="namaMapel in mapelByKategori[kategori]">
                                    <span
                                        class="px-2 py-0.5 text-[11px] rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 cursor-pointer hover:bg-emerald-100 dark:hover:bg-emerald-800 transition"
                                        @click="tambahDariPreview(namaMapel)"
                                        x-text="namaMapel"
                                    ></span>
                                </template>
                            </div>
                        </div>
                        <div class="mt-2" x-show="!kategori || !mapelByKategori[kategori]?.length" x-transition.opacity>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ __('auth.kategori_mapel_preview_empty') }}</p>
                        </div>
                        @error('kategori_mapel')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form-input
                        name="jenjang_dan_tingkat"
                        :label="__('auth.jenjang_dan_tingkat_label')"
                        value="{{ old('jenjang_dan_tingkat') }}"
                        placeholder="{{ __('auth.jenjang_dan_tingkat_placeholder') }}"
                    />

                    {{-- Mata pelajaran: chip array, dikirim sebagai input tersembunyi --}}
                    <div class="mb-3">
                        <label for="mapel_input" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.mata_pelajaran_label') }}
                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('auth.opsional') }})</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                id="mapel_input"
                                type="text"
                                x-model="mapelInput"
                                @keydown.enter.prevent="tambahMapel()"
                                maxlength="255"
                                placeholder="{{ __('auth.mata_pelajaran_placeholder') }}"
                                class="flex-1 min-w-0 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400"
                            >
                            <button
                                type="button"
                                @click="tambahMapel()"
                                title="{{ __('auth.mata_pelajaran_tambah') }}"
                                class="px-3.5 py-2 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shrink-0"
                            >
                                +
                            </button>
                        </div>
                        <template x-for="(m, i) in mapel" :key="i">
                            <input type="hidden" name="mata_pelajaran[]" :value="m">
                        </template>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="(m, i) in mapel" :key="'c' + i">
                                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 text-xs rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <span x-text="m"></span>
                                    <button
                                        type="button"
                                        @click="mapel.splice(i, 1)"
                                        class="w-4 h-4 rounded-full flex items-center justify-center text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800"
                                        aria-label="Hapus"
                                    >
                                        &times;
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="cv" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                {{ __('auth.cv_label') }}
                                <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('auth.file_hint') }})</span>
                            </label>
                            <input
                                type="file"
                                name="cv"
                                id="cv"
                                accept=".pdf,.doc,.docx"
                                class="block w-full text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 cursor-pointer"
                            >
                            @error('cv')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ijazah" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                {{ __('auth.ijazah_label') }}
                                <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('auth.file_hint') }})</span>
                            </label>
                            <input
                                type="file"
                                name="ijazah"
                                id="ijazah"
                                accept=".pdf,.doc,.docx"
                                class="block w-full text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 cursor-pointer"
                            >
                            @error('ijazah')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-form-section>
            </template>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('mapelPreview', () => {
                        const data = JSON.parse(document.getElementById('mapel-by-kategori').textContent);
                        return {
                            kategori: @json(old('kategori_mapel')),
                            mapelByKategori: data || {},
                        };
                    });
                });
            </script>

            <div
                x-show="role === 'pelajar'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                <x-form-input
                    name="nisn"
                    label="NISN"
                    value="{{ old('nisn') }}"
                    placeholder="{{ __('auth.nisn_placeholder') }}"
                    x-bind:required="role === 'pelajar' && jalurKode === 'formal'"
                    aria-describedby="nisn-hint"
                    icon='<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>'
                />
                <p id="nisn-hint" class="text-xs text-slate-600 dark:text-slate-400">{{ __('auth.nisn_formal_hint') }}</p>
            </div>

            {{-- Profil pendidikan pelajar: cascading jalur → jenjang → tingkat.
                 x-if (bukan x-show) supaya field-nya tidak ikut terkirim saat
                 role pengajar dipilih. --}}
            <template x-if="role === 'pelajar'">
                <x-form-section
                    :title="__('auth.profil_pendidikan')"
                    :hint="__('auth.profil_pendidikan_hint')"
                >
                    <div class="mb-3">
                        <label for="jalur_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.jalur_label') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="jalur_id"
                            id="jalur_id"
                            x-model="jalurId"
                            @change="jenjangId = ''; tingkatId = ''"
                            required
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('jalur_id') border-red-400 @enderror"
                        >
                            <option value="">{{ __('komponen.pilih_opsi') }}</option>
                            @foreach ($jalurPayload as $j)
                                <option value="{{ $j['id'] }}">{{ $j['nama'] }}</option>
                            @endforeach
                        </select>
                        <p
                            x-show="jalurKode === 'self_learning'"
                            x-cloak
                            class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            {{ __('auth.belajar_mandiri_hint') }}
                        </p>
                        @error('jalur_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3" x-show="jenjangList.length > 0" x-transition.opacity>
                        <label for="jenjang_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.jenjang_label') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="jenjang_id"
                            id="jenjang_id"
                            x-model="jenjangId"
                            @change="tingkatId = ''"
                            :required="jalurKode === 'formal' || jalurKode === 'nonformal'"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('jenjang_id') border-red-400 @enderror"
                        >
                            <option value="">{{ __('komponen.pilih_opsi') }}</option>
                            <template x-for="j in jenjangList" :key="j.id">
                                <option :value="j.id" x-text="j.nama"></option>
                            </template>
                        </select>
                        @error('jenjang_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3" x-show="tingkatList.length > 0" x-transition.opacity>
                        <label for="tingkat_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.tingkat_label') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tingkat_id"
                            id="tingkat_id"
                            x-model="tingkatId"
                            :required="jalurKode === 'formal' || jalurKode === 'nonformal'"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error('tingkat_id') border-red-400 @enderror"
                        >
                            <option value="">{{ __('komponen.pilih_opsi') }}</option>
                            <template x-for="t in tingkatList" :key="t.id">
                                <option :value="t.id" x-text="t.nama"></option>
                            </template>
                        </select>
                        @error('tingkat_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3" x-show="butuhInstitusi" x-transition.opacity>
                        <label for="institusi" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.institusi_label') }}
                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('auth.opsional') }})</span>
                        </label>
                        <input
                            type="text"
                            name="institusi"
                            id="institusi"
                            value="{{ old('institusi') }}"
                            maxlength="255"
                            placeholder="{{ __('auth.institusi_placeholder') }}"
                            class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 @error('institusi') border-red-400 @enderror"
                        >
                        @error('institusi')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3" x-show="jalurKode === 'nonformal'" x-transition.opacity>
                        <label for="program" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.program_label') }}
                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('auth.opsional') }})</span>
                        </label>
                        <input
                            type="text"
                            name="program"
                            id="program"
                            value="{{ old('program') }}"
                            maxlength="255"
                            placeholder="{{ __('auth.program_placeholder') }}"
                            class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 @error('program') border-red-400 @enderror"
                        >
                        @error('program')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-1">
                        <label for="minat" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            {{ __('auth.minat_label') }}
                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">({{ __('auth.opsional') }})</span>
                        </label>
                        <input
                            type="text"
                            name="minat"
                            id="minat"
                            value="{{ old('minat') }}"
                            maxlength="500"
                            placeholder="{{ __('auth.minat_placeholder') }}"
                            class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 @error('minat') border-red-400 @enderror"
                        >
                        @error('minat')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </x-form-section>
            </template>

            <x-form-password
                name="password"
                :label="__('auth.password_label')"
                :placeholder="__('auth.password_placeholder')"
                required
                :minlength="8"
                autocomplete="new-password"
            />

            <x-form-password
                name="password_confirmation"
                :label="__('auth.password_confirm')"
                :placeholder="__('auth.password_confirm_placeholder')"
                required
                autocomplete="new-password"
            />

            <x-btn-primary type="submit" class="w-full">
                {{ __('auth.register_button') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </x-btn-primary>
        </form>

        <div class="border-t border-slate-200 dark:border-slate-800 my-5"></div>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('auth.have_account') }}
            <a
                href="{{ route('login') }}"
                class="font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition"
            >
                {{ __('auth.login_link') }}
            </a>
        </p>
    </div>
@endsection
