{{--
    Pemilih konteks generate soal: pertemuan, materi, lalu potongan dokumen.

    Seluruh state milik Alpine builder (kuisBuilder / tugasBuilder) lewat
    resources/js/pages/konteks-soal.js, jadi komponen ini hanya merender dan
    menyuntik angka yang sama dengan yang divalidasi server.
--}}
@php
    use App\Services\KuisGeneratorService;
    use App\Support\KonteksBatas;

    $sumberKonteks = $sumberKonteks ?? [];
    $ragAktif = (bool) config('services.kuis_rag.enabled', true);
    $punyaPotongan = collect($sumberKonteks)->contains(
        fn (array $pertemuan): bool => collect($pertemuan['materi'] ?? [])->contains('punya_chunk', true)
    );
    $bolehPotongan = $ragAktif && $punyaPotongan;

    // Angka dan teks pesan yang sama dipakai service saat menyusun prompt, jadi
    // counter di UI tidak pernah menjanjikan sesuatu yang nanti dipotong server.
    $konfigurasi = [
        'batas' => KonteksBatas::CHUNK,
        'perPotongan' => KuisGeneratorService::KHAR_PER_CHUNK,
        'plafon' => KuisGeneratorService::KHAR_MANUAL,
        'anggaran' => (int) config('services.kuis_rag.max_chars', 8000),
        'pesan' => __('komponen.konteks_chunk_pesan'),
    ];
@endphp

<div class="space-y-4">
    <script type="application/json" id="konteks-chunk-konfig">@json($konfigurasi)</script>

    <fieldset class="space-y-3">
        <legend class="text-xs font-medium text-slate-600 dark:text-slate-400">
            {{ __('komponen.konteks_materi') }}
        </legend>

        <div class="flex flex-wrap items-start justify-between gap-2">
            <p class="max-w-prose text-xs text-slate-600 dark:text-slate-400">
                {{ __('komponen.bantuan_konteks_materi') }}
            </p>
            <button
                type="button"
                x-show="konteksPertemuan.length > 0"
                x-cloak
                @click="resetKonteks()"
                class="min-h-11 shrink-0 text-xs font-medium text-emerald-700 hover:text-emerald-800 focus:ring-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300"
            >
                {{ __('komponen.kembalikan_ke_pertemuan') }}
            </button>
        </div>

        <p class="text-xs text-slate-600 dark:text-slate-400">
            {{ __('komponen.batas_pilihan_konteks', [
                'pertemuan' => KonteksBatas::PERTEMUAN,
                'materi' => KonteksBatas::MATERI,
                'potongan' => KonteksBatas::CHUNK,
            ]) }}
        </p>

        @if (count($sumberKonteks) === 0)
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ __('komponen.konteks_tidak_ada_pertemuan') }}
            </p>
        @else
            <div class="space-y-2">
                <template x-for="p in sumberKonteks" :key="p.id">
                    <div class="rounded-lg border border-slate-200 dark:border-slate-800">
                        <div class="flex items-start gap-2 px-3 py-2">
                            <input
                                type="checkbox"
                                :id="'pertemuan-konteks-' + p.id"
                                :checked="pertemuanDipilih(p.id)"
                                @change="togglePertemuanKonteks(p.id)"
                                class="mt-1 shrink-0 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500 dark:border-slate-600 dark:text-emerald-400"
                            >
                            <label :for="'pertemuan-konteks-' + p.id" class="min-w-0 flex-1 cursor-pointer">
                                <span class="block text-sm font-medium text-slate-800 dark:text-slate-200" x-text="p.nama"></span>
                                <span class="block text-xs text-slate-600 dark:text-slate-400" x-text="p.kelas_nama"></span>
                            </label>
                            <button
                                type="button"
                                @click="balikPanelMateri(p.id)"
                                :aria-expanded="panelMateriTerbuka(p.id) ? 'true' : 'false'"
                                :aria-controls="'materi-pertemuan-' + p.id"
                                x-show="materiDari(p.id).length > 0"
                                class="min-h-11 shrink-0 rounded-md px-2 text-slate-600 hover:text-emerald-700 focus:ring-emerald-500 dark:text-slate-400 dark:hover:text-emerald-400"
                            >
                                <span class="sr-only">{{ __('komponen.tampilkan_materi') }}</span>
                                <svg class="h-4 w-4 transition-transform" :class="panelMateriTerbuka(p.id) ? '' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <div
                            :id="'materi-pertemuan-' + p.id"
                            x-show="pertemuanDipilih(p.id) && panelMateriTerbuka(p.id)"
                            x-cloak
                            class="space-y-1 border-t border-slate-100 px-3 py-2 dark:border-slate-800"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    <span x-show="jumlahMateriDipilih(p.id) > 0" x-cloak>
                                        <span x-text="jumlahMateriDipilih(p.id)"></span> / <span x-text="materiDari(p.id).length"></span>
                                        {{ __('komponen.materi_dicentang') }}
                                    </span>
                                    <span x-show="jumlahMateriDipilih(p.id) === 0">{{ __('komponen.materi_kosong_pertemuan') }}</span>
                                </p>
                                <button
                                    type="button"
                                    @click="isiSemuaMateri(p.id)"
                                    class="min-h-11 text-xs font-medium text-emerald-700 hover:text-emerald-800 focus:ring-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300"
                                >
                                    <span x-show="!semuaMateriTerpilih(p.id)">{{ __('komponen.pilih_semua_materi') }}</span>
                                    <span x-show="semuaMateriTerpilih(p.id)" x-cloak>{{ __('komponen.lepaskan_semua_materi') }}</span>
                                </button>
                            </div>

                            <template x-for="m in materiDari(p.id)" :key="m.id">
                                <label class="flex cursor-pointer items-start gap-2">
                                    <input
                                        type="checkbox"
                                        :checked="materiDipilih(m.id)"
                                        @change="toggleMateriKonteks(m.id)"
                                        class="mt-1 shrink-0 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500 dark:border-slate-600 dark:text-emerald-400"
                                    >
                                    <span class="min-w-0 text-sm text-slate-700 dark:text-slate-300">
                                        <span x-text="m.judul"></span>
                                        @if ($bolehPotongan)
                                            <span x-show="!m.punya_chunk" x-cloak class="text-xs text-slate-600 dark:text-slate-400">
                                                ({{ __('komponen.materi_tanpa_dokumen') }})
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    </fieldset>

    @if ($bolehPotongan)
        <div class="rounded-lg border border-slate-200 dark:border-slate-800">
            <button
                type="button"
                @click="chunkPanel()"
                :aria-expanded="chunkSiap ? 'true' : 'false'"
                aria-controls="daftar-potongan-konteks"
                class="flex min-h-11 w-full items-center justify-between gap-3 px-3 py-2 text-left"
            >
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-slate-800 dark:text-slate-200">{{ __('komponen.potongan_dokumen') }}</span>
                    <span class="block text-xs text-slate-600 dark:text-slate-400">{{ __('komponen.potongan_dokumen_singkat') }}</span>
                </span>
                <span role="status" aria-live="polite" class="shrink-0 text-xs font-medium text-slate-700 dark:text-slate-300" x-text="chunkRingkasHeader()"></span>
            </button>

            <div id="daftar-potongan-konteks" x-show="chunkSiap" x-cloak class="space-y-3 border-t border-slate-100 px-3 py-3 dark:border-slate-800">
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    {{ __('komponen.potongan_dokumen_bantu') }}
                </p>

                <div class="flex flex-wrap items-end gap-2">
                    <div class="min-w-40 flex-1">
                        <label for="chunk_kata" class="block text-xs font-medium text-slate-600 dark:text-slate-400">
                            {{ __('komponen.topik_potongan') }}
                        </label>
                        <input
                            id="chunk_kata"
                            type="text"
                            x-model="chunkKata"
                            placeholder="{{ __('komponen.topik_potongan_placeholder') }}"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700"
                        >
                    </div>
                    <x-btn-secondary
                        size="sm"
                        color="indigo"
                        class="min-h-11"
                        x-bind:disabled="chunkSedangMengisi"
                        @click="chunkIsiOtomatis()"
                    >
                        <span x-show="!chunkSedangMengisi">{{ __('komponen.isi_otomatis') }}</span>
                        <span x-show="chunkSedangMengisi" x-cloak>{{ __('komponen.isi_otomatis_sibuk') }}</span>
                    </x-btn-secondary>
                </div>

                <p x-show="chunkPesan" x-cloak role="status" class="text-xs font-medium text-amber-800 dark:text-amber-400" x-text="chunkPesan"></p>
                <p x-show="chunkPesanHilang()" x-cloak role="status" class="text-xs font-medium text-amber-800 dark:text-amber-400" x-text="chunkPesanHilang()"></p>

                <p x-show="chunkSibuk" x-cloak role="status" class="text-xs text-slate-600 dark:text-slate-400">
                    <span class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2 border-slate-300 border-t-emerald-600 align-[-2px] dark:border-slate-600" aria-hidden="true"></span>
                    {{ __('komponen.muat_potongan') }}
                </p>

                <div x-show="!chunkSibuk && chunkDaftar.length > 0" class="max-h-96 divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                    <template x-for="c in chunkDaftar" :key="c.id">
                        <label class="flex cursor-pointer items-start gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <input
                                type="checkbox"
                                :checked="chunkDipilih(c.id)"
                                @change="chunkToggle(c.id)"
                                :disabled="!chunkDipilih(c.id) && chunkSisa() === 0"
                                class="mt-1 shrink-0 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500 disabled:opacity-40 dark:border-slate-600 dark:text-emerald-400"
                            >
                            <span class="min-w-0">
                                <span class="block text-xs font-medium text-slate-800 dark:text-slate-200">
                                    <span x-text="c.materi_judul"></span>
                                    <span class="text-slate-600 dark:text-slate-400" x-text="'· ' + c.dokumen"></span>
                                </span>
                                <span class="block text-xs text-slate-600 dark:text-slate-400">
                                    <span x-text="chunkMeta(c)"></span>
                                </span>
                                <span class="mt-1 block text-xs text-slate-700 dark:text-slate-300" x-text="c.cuplikan"></span>
                            </span>
                        </label>
                    </template>
                </div>

                <p x-show="chunkSudahDimuat && !chunkSibuk && chunkDaftar.length === 0" class="text-sm text-slate-600 dark:text-slate-400">
                    {{ __('komponen.potongan_kosong') }}
                </p>

                <div x-show="chunkHalamanAkhir > 1" class="flex items-center justify-between gap-2">
                    <x-btn-secondary size="sm" class="min-h-11" x-bind:disabled="chunkHalaman <= 1 || chunkSibuk" @click="chunkMuat(chunkHalaman - 1)">
                        {{ __('komponen.halaman_sebelumnya') }}
                    </x-btn-secondary>
                    <span role="status" aria-live="polite" class="text-xs text-slate-600 dark:text-slate-400" x-text="chunkTeksHalaman()"></span>
                    <x-btn-secondary size="sm" class="min-h-11" x-bind:disabled="chunkHalaman >= chunkHalamanAkhir || chunkSibuk" @click="chunkMuat(chunkHalaman + 1)">
                        {{ __('komponen.halaman_berikutnya') }}
                    </x-btn-secondary>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap gap-2">
                        <x-btn-secondary size="sm" class="min-h-11" x-bind:disabled="chunkSisa() === 0 || chunkDaftar.length === 0" @click="chunkPilihHalaman()">
                            {{ __('komponen.pilih_halaman') }}
                        </x-btn-secondary>
                        <x-btn-secondary size="sm" color="red" class="min-h-11" x-bind:disabled="chunkJumlah() === 0" @click="chunkKosongkan()">
                            {{ __('komponen.lepaskan_potongan') }}
                        </x-btn-secondary>
                    </div>
                    <p role="status" aria-live="polite" class="text-xs text-slate-600 dark:text-slate-400" x-text="chunkTeksEstimasi()"></p>
                </div>

                <p x-show="chunkTerpotong()" x-cloak role="status" class="text-xs font-medium text-amber-800 dark:text-amber-400">
                    {{ __('komponen.potongan_estimasi_lebih') }}
                </p>

                @if ($ragAktif)
                    <div>
                        <label for="rag_top_k" class="block text-xs font-medium text-slate-600 dark:text-slate-400">
                            {{ __('komponen.rag_top_k') }}
                        </label>
                        <select
                            id="rag_top_k"
                            x-model="ragTopK"
                            aria-describedby="rag_top_k_help"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-48 dark:border-slate-700"
                        >
                            <option value="">{{ __('komponen.rag_top_k_default', ['count' => (int) config('services.kuis_rag.top_k', 4)]) }}</option>
                            @for ($k = 1; $k <= 10; $k++)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endfor
                        </select>
                        <p id="rag_top_k_help" class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                            {{ __('komponen.rag_top_k_help') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <p class="text-xs text-emerald-800 dark:text-emerald-400">
        {{ __('komponen.konteks_dipakai') }} <span x-text="ringkasanKonteks()"></span>
    </p>

    <template x-for="id in konteksPertemuan" :key="'kp-' + id">
        <input type="hidden" name="konteks_pertemuan_ids[]" :value="id">
    </template>
    <template x-for="id in konteksMateri" :key="'km-' + id">
        <input type="hidden" name="konteks_materi_ids[]" :value="id">
    </template>
    <template x-for="id in chunkIds()" :key="'kc-' + id">
        <input type="hidden" name="konteks_chunk_ids[]" :value="id">
    </template>
    @if ($ragAktif)
        <input type="hidden" name="rag_top_k" :value="ragTopK">
    @endif
</div>
