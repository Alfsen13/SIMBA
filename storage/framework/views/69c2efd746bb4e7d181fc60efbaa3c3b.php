<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'subtitle' => '',
    'actions' => null,
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
    'title' => '',
    'subtitle' => '',
    'actions' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div>
        <h1 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e($title); ?></h1>
        <?php if($subtitle): ?>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e($subtitle); ?></p>
        <?php endif; ?>
    </div>
    <?php if($actions): ?>
        <div class="flex items-center gap-2 shrink-0">
            <?php echo e($actions); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\page-header.blade.php ENDPATH**/ ?>