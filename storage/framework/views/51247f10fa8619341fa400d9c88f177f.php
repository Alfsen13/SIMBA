<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => null,
    'message' => __('komponen.tidak_ada_data'),
    'action' => null,
    'actionLabel' => '',
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
    'icon' => null,
    'message' => __('komponen.tidak_ada_data'),
    'action' => null,
    'actionLabel' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex flex-col items-center justify-center py-8">
    <?php if($icon): ?>
        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center mb-3 ring-1 ring-slate-200 dark:ring-slate-700">
            <?php echo $icon; ?>

        </div>
    <?php endif; ?>
    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs text-center"><?php echo e($message); ?></p>
    <?php if($action && $actionLabel): ?>
        <a href="<?php echo e($action); ?>" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
            <?php echo e($actionLabel); ?>

            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\empty-state.blade.php ENDPATH**/ ?>