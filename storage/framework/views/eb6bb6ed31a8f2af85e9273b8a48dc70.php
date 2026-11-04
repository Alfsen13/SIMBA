<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    // Nama variabel Alpine yang berisi objek soal ('q' untuk PG, 's' untuk esai).
    'soal' => 'q',
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
    // Nama variabel Alpine yang berisi objek soal ('q' untuk PG, 's' untuk esai).
    'soal' => 'q',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<template x-if="<?php echo e($soal); ?>.akurasi_materi !== undefined && <?php echo e($soal); ?>.akurasi_materi !== null">
    <div class="mb-2 flex flex-wrap items-center gap-2">
        <span
            title="<?php echo e(__('guru_soal.akurasi_help')); ?>"
            class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-medium"
            :class="<?php echo e($soal); ?>.akurasi_materi >= 80 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30' : (<?php echo e($soal); ?>.akurasi_materi >= 60 ? 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30' : 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30')"
        >
            <?php echo e(__('guru_soal.akurasi_materi')); ?>: <span x-text="<?php echo e($soal); ?>.akurasi_materi"></span>%
        </span>
        <span
            x-show="<?php echo e($soal); ?>.catatan_akurasi"
            x-cloak
            class="text-xs text-slate-500 dark:text-slate-400"
            x-text="<?php echo e($soal); ?>.catatan_akurasi"
        ></span>
    </div>
</template>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\akurasi-soal.blade.php ENDPATH**/ ?>