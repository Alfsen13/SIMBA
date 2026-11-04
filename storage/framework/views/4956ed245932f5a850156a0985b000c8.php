<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'judul',
    'heading' => __('komponen.ai_sedang_menyusun'),
    'catatan' => __('komponen.catatan_waktu_proses'),
    'footer' => __('komponen.mohon_tunggu'),
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'judul',
    'heading' => __('komponen.ai_sedang_menyusun'),
    'catatan' => __('komponen.catatan_waktu_proses'),
    'footer' => __('komponen.mohon_tunggu'),
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-show="generating"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
>
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md p-6 text-center">
        <div class="mx-auto w-14 h-14 mb-5">
            <svg class="w-14 h-14 text-emerald-700 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white"><?php echo e($heading); ?> <?php echo e($judul); ?></h3>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            <?php echo e($catatan); ?>

        </p>
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400"><?php echo e($footer); ?></p>
    </div>
</div><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\loading-overlay.blade.php ENDPATH**/ ?>