@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
            {{ $label }}
            @if($required)<span class="text-red-600 dark:text-red-400">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 @error($name) border-red-400 @enderror disabled:opacity-50 disabled:cursor-not-allowed"
        {{ $attributes->except(['class', 'name', 'id', 'rows', 'placeholder', 'required']) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
