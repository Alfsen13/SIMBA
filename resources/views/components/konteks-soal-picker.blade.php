{{--
    Pilihan konteks materi untuk generate soal dengan AI.

    Guru bisa mencentang beberapa pertemuan, lalu mempersempit ke materi tertentu
    di dalam pertemuan tersebut. Kosongkan semuanya untuk memakai pertemuan
    tempat kuis/tugas ini disimpan (perilaku bawaan).

    Semua state dibaca dari Alpine component builder (kuisBuilder / tugasBuilder)
    lewat konteks-soal.js, jadi markup ini cukup dipanggil di form mana pun.
--}}
@php
    $sumberKonteks = $sumberKonteks ?? [];
@endphp

<div class="mb-3">
    <div class="flex items-center justify-between mb-1">
        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">
            {{ __('komponen.konteks_materi') }}
        </label>
        <button
            type="button"
            x-show="konteksPertemuan.length > 0"
            x-cloak
            @click="resetKonteks()"
            class="text-xs text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
        >
            {{ __('komponen.kembalikan_ke_pertemuan') }}
        </button>
    </div>
    <div class="mb-3">
        <label for="rag_top_k" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
            {{ __('komponen.rag_top_k') }}
        </label>
        <select id="rag_top_k" x-model="ragTopK" aria-describedby="rag_top_k_help"
            class="block w-full sm:w-48 rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">{{ __('komponen.rag_top_k_default', ['count' => (int) config('services.kuis_rag.top_k', 4)]) }}</option>
            @for ($k = 1; $k <= 10; $k++)
                <option value="{{ $k }}">{{ $k }}</option>
            @endfor
        </select>
        <p id="rag_top_k_help" class="mt-1 text-xs text-slate-600 dark:text-slate-400">
            {{ __('komponen.rag_top_k_help') }}
        </p>
    </div>
    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
        {{ __('komponen.bantuan_konteks_materi') }}
    </p>

    @if (count($sumberKonteks) === 0)
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ __('komponen.konteks_tidak_ada_pertemuan') }}
        </p>
    @else
        <div class="rounded-lg border border-slate-200 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
            <template x-for="p in sumberKonteks" :key="p.id">
                <div class="px-3 py-2">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            class="mt-0.5 shrink-0 rounded border-slate-300 dark:border-slate-600 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500"
                            :checked="pertemuanDipilih(p.id)"
                            @change="togglePertemuanKonteks(p.id)"
                        >
                        <span class="text-sm text-slate-800 dark:text-slate-200">
                            <span x-text="p.nama"></span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400" x-text="p.kelas_nama"></span>
                        </span>
                    </label>

                    <div x-show="pertemuanDipilih(p.id) && materiDari(p.id).length > 0" x-cloak class="ml-6 mt-2">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                            <span x-text="jumlahMateriDipilih(p.id) === 0 ? 'Kosong = pakai seluruh materi' : jumlahMateriDipilih(p.id) + ' dari ' + materiDari(p.id).length + ' materi dipilih'"></span>
                        </p>
                        <div class="space-y-1">
                            <template x-for="m in materiDari(p.id)" :key="m.id">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        class="mt-0.5 shrink-0 rounded border-slate-300 dark:border-slate-600 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500"
                                        :checked="materiDipilih(m.id)"
                                        @change="toggleMateriKonteks(m.id)"
                                    >
                                    <span class="text-sm text-slate-700 dark:text-slate-300" x-text="m.judul"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-2">
            {{ __('komponen.konteks_dipakai') }} <span x-text="ringkasanKonteks()"></span>
        </p>
    @endif
</div>
