@php use Illuminate\Support\Js; @endphp

@if(session('success'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
        class="flex items-start gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
        role="alert"
    >
        <svg class="w-5 h-5 text-green-700 dark:text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="flex-1 text-green-800 dark:text-green-300">{{ session('success') }}</span>
        <button @click="show = false" class="text-green-700 hover:text-green-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

@if(session('error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
        class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
        role="alert"
    >
        <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="flex-1 text-red-800 dark:text-red-300">{{ session('error') }}</span>
        <button @click="show = false" class="text-red-600 dark:text-red-400 hover:text-red-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

@if(session('info'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
        class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
        role="status"
    >
        <svg class="w-5 h-5 text-blue-700 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="flex-1 text-blue-800 dark:text-blue-300">{{ session('info') }}</span>
        <button @click="show = false" class="text-blue-700 hover:text-blue-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

{{-- Flash `warning` dipakai untuk hasil yang BERHASIL tetapi perlu dibaca guru,
     mis. prediksi ML jatuh ke perhitungan manual karena model belum dilatih.
     Sengaja TIDAK auto-hide seperti success/error: kalau guru mengabaikannya,
     dia bisa mengira prediksi came dari model padahal tidak. Hanya tombol X. --}}
@if(session('warning'))
    <div
        x-data="{ show: true }"
        x-show="show"
        class="flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
        role="alert"
    >
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div class="flex-1">
            @if(is_array(session('warning')))
                <p class="font-medium text-amber-800 dark:text-amber-300">{{ __('common.attention') }}</p>
                <ul class="list-disc list-inside text-amber-700 dark:text-amber-400 mt-1 space-y-0.5">
                    @foreach(session('warning') as $w)
                        <li>{{ $w }}</li>
                    @endforeach
                </ul>
            @else
                <span class="text-amber-800 dark:text-amber-300">{{ session('warning') }}</span>
            @endif
        </div>
        <button @click="show = false" class="text-amber-600 dark:text-amber-400 hover:text-amber-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

@if(session('import_errors'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
        class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
        role="alert"
    >
        <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div class="flex-1">
            <p class="font-medium text-red-800 dark:text-red-300">{{ __('common.import_errors') }}</p>
            <ul class="list-disc list-inside text-red-700 dark:text-red-400 mt-1 space-y-0.5">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        <button @click="show = false" class="text-red-600 hover:text-red-700 transition-colors shrink-0 self-start mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif

@if($errors->any())
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
        class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
        role="alert"
    >
        <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="font-medium text-red-800 dark:text-red-300">{{ __('common.fix_errors') }}</p>
            <ul class="list-disc list-inside text-red-700 dark:text-red-400 mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button @click="show = false" class="text-red-600 hover:text-red-700 transition-colors shrink-0 self-start mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
@endif
