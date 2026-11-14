{{--
    Pemilih konteks generate soal: pertemuan dan materi.

    Seluruh state milik Alpine builder (kuisBuilder / tugasBuilder) lewat
    resources/js/pages/konteks-soal.js, jadi komponen ini hanya merender dan
    menyuntik angka yang sama dengan yang divalidasi server.
--}}
@php
    use App\Support\KonteksBatas;
@endphp

<div class="space-y-4">
    <script type="application/json" id="konteks-soal-konfig">@json(['pesan' => __('komponen.konteks_pesan')])</script>

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
                                        <span x-show="!m.punya_chunk" x-cloak class="text-xs text-slate-600 dark:text-slate-400">
                                            ({{ __('komponen.materi_tanpa_dokumen') }})
                                        </span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    </fieldset>

    <p class="text-xs text-emerald-800 dark:text-emerald-400">
        {{ __('komponen.konteks_dipakai') }} <span x-text="ringkasanKonteks()"></span>
    </p>

    <template x-for="id in konteksPertemuan" :key="'kp-' + id">
        <input type="hidden" name="konteks_pertemuan_ids[]" :value="id">
    </template>
    <template x-for="id in konteksMateri" :key="'km-' + id">
        <input type="hidden" name="konteks_materi_ids[]" :value="id">
    </template>
</div>
