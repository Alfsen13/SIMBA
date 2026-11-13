@props(['value'])

{{-- `value` adalah ekspresi Alpine (mis. "q.pertanyaan"), bukan teks literal,
     supaya pesan dan tombol ikut diperbarui saat guru mengetik. --}}
<div class="mt-1 space-y-1">
    <template x-for="(masalah, index) in periksaRumus({{ $value }})" :key="index">
        <p class="text-xs text-amber-700 dark:text-amber-400" x-text="masalah.message"></p>
    </template>

    <button
        type="button"
        x-show="akanDirapikan({{ $value }})"
        x-cloak
        x-on:click="{{ $value }} = rapikanRumus({{ $value }})"
        class="inline-flex min-h-11 items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline"
    >
        {{ __('guru_soal.rumus_rapikan') }}
    </button>
</div>
