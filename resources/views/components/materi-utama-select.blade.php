@props([
    'value' => null,
    'pendukung' => [],
])

{{--
    Pemilihan materi untuk kuis & tugas. Dua konsep, sengaja dipisah:

    1. MATERI UTAMA (`materi_id`, kolom tunggal)
       Satu materi, dan WAJIB milik pertemuan kuis/tugas ini sendiri karena
       opsinya diambil dari `materiDari()`.

    2. MATERI PENDUKUNG (`materi_ids[]`, tabel pivot kuis_materi/tugas_materi)
       Banyak materi, boleh dari pertemuan lain. Ini yang bikin atribusi nilai
       jadi multi-materi: satu kuis bisa menyasar beberapa materi sekaligus
       tanpa ada yang hilang.

    Keduanya opsional. Kalau kosong semua, atribusi nilai jatuh ke tingkat
    pertemuan (lihat RemedialService::getWeakTopics).

    Opsi diambil dari `sumberKonteks` yang sudah dikirim controller ke view,
    jadi tidak ada query tambahan.
--}}

<div class="mb-5">
    <label for="materi_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
        {{ __('komponen.materi_utama') }} <span class="font-normal text-slate-500 dark:text-slate-400">{{ __('komponen.opsional') }}</span>
    </label>

    <select
        name="materi_id"
        id="materi_id"
        x-model="materiUtama"
        class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-150 @error('materi_id') border-red-400 @enderror"
    >
        <option value="">{{ __('komponen.tanpa_materi_utama') }}</option>

        <template x-for="m in materiDari(selectedPertemuan || pertemuanId)" :key="m.id">
            <option :value="String(m.id)" x-text="m.judul"></option>
        </template>
    </select>

    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
        {{ __('komponen.bantuan_materi_utama') }}
    </p>

    @error('materi_id')
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>

<div class="mb-5">
    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
        {{ __('komponen.materi_pendukung') }} <span class="font-normal text-slate-500 dark:text-slate-400">{{ __('komponen.opsional_boleh_banyak') }}</span>
    </label>

    {{-- Checkbox dengan name="materi_ids[]" supaya terkirim sebagai array
         tanpa perlu serialisasi JSON di sisi client. --}}
    <div
        class="max-h-56 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-3 space-y-1.5"
        x-show="semuaMateri().length > 0"
    >
        <template x-for="m in semuaMateri()" :key="m.id">
            <label class="flex items-start gap-2.5 cursor-pointer text-sm text-slate-700 dark:text-slate-300">
                <input
                    type="checkbox"
                    name="materi_ids[]"
                    :value="String(m.id)"
                    x-model="materiPendukung"
                    class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500"
                >
                <span class="leading-snug">
                    <span x-text="m.judul"></span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400" x-text="m.pertemuan ? m.pertemuan : ''"></span>
                </span>
            </label>
        </template>
    </div>

    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
        {{ __('komponen.bantuan_materi_pendukung') }}
    </p>

    @error('materi_ids')
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror

    @error('materi_ids.*')
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
