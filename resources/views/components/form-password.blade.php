@props([
    'name' => 'password',
    'label' => '',
    'placeholder' => '',
    'required' => false,
    'minlength' => null,
    'autocomplete' => 'current-password',
])

{{-- Input kata sandi dengan toggle lihat/sembunyi + pesan error per-field.
     Dipakai halaman login & register supaya markup-nya tidak diduplikasi. --}}
<div {{ $attributes }}>
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
        {{ $label !== '' ? $label : __('auth.password_label') }}
        @if ($required)<span class="text-red-600 dark:text-red-400">*</span>@endif
    </label>

    <div class="relative" x-data="{ show: false }">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <input
            :type="show ? 'text' : 'password'"
            name="{{ $name }}"
            id="{{ $name }}"
            autocomplete="{{ $autocomplete }}"
            @if ($required) required @endif
            @if ($minlength) minlength="{{ $minlength }}" @endif
            placeholder="{{ $placeholder }}"
            class="w-full pl-9 pr-10 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 @error($name) border-red-300 dark:border-red-700 @enderror"
            {{ $attributes->except(['class', 'type', 'name', 'id', 'autocomplete', 'required', 'minlength', 'placeholder']) }}>

        <button
            type="button"
            @click="show = !show"
            :aria-label="show ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 dark:text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition"
        >
            <svg x-show="!show" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg x-show="show" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
        </button>
    </div>

    @error($name)
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1" role="alert">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
