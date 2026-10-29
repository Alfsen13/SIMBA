<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'hint' => null,
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
    'title',
    'hint' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div <?php echo e($attributes->merge(['class' => 'pt-1'])); ?>>
    <div class="mb-3">
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300"><?php echo e($title); ?></p>
        <?php if($hint): ?>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e($hint); ?></p>
        <?php endif; ?>
    </div>

    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views/components/form-section.blade.php ENDPATH**/ ?>