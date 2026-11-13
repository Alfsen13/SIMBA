@props(['value'])

<div x-show="hasMath({{ $value }})" x-cloak class="mt-2 text-sm text-slate-700 dark:text-slate-300">
    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('guru_soal.pratinjau_rumus') }}:</span>
    <div class="overflow-x-auto" x-html="mathPreview({{ $value }})"></div>
</div>
