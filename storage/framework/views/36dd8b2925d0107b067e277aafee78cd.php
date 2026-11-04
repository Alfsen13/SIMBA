<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => null,
    'pendukung' => [],
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
    'value' => null,
    'pendukung' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div class="mb-5">
    <label for="materi_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
        <?php echo e(__('komponen.materi_utama')); ?> <span class="font-normal text-slate-500 dark:text-slate-400"><?php echo e(__('komponen.opsional')); ?></span>
    </label>

    <select
        name="materi_id"
        id="materi_id"
        x-model="materiUtama"
        class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-150 <?php $__errorArgs = ['materi_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
    >
        <option value=""><?php echo e(__('komponen.tanpa_materi_utama')); ?></option>

        <template x-for="m in materiDari(selectedPertemuan || pertemuanId)" :key="m.id">
            <option :value="String(m.id)" x-text="m.judul"></option>
        </template>
    </select>

    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
        <?php echo e(__('komponen.bantuan_materi_utama')); ?>

    </p>

    <?php $__errorArgs = ['materi_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <?php echo e($message); ?>

        </p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

<div class="mb-5">
    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
        <?php echo e(__('komponen.materi_pendukung')); ?> <span class="font-normal text-slate-500 dark:text-slate-400"><?php echo e(__('komponen.opsional_boleh_banyak')); ?></span>
    </label>

    
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
        <?php echo e(__('komponen.bantuan_materi_pendukung')); ?>

    </p>

    <?php $__errorArgs = ['materi_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <?php echo e($message); ?>

        </p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    <?php $__errorArgs = ['materi_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <?php echo e($message); ?>

        </p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\materi-utama-select.blade.php ENDPATH**/ ?>