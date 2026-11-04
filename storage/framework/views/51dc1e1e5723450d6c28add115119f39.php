<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'class' => '',
    'align' => 'left',
    'nowrap' => false,
    'muted' => false,
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
    'class' => '',
    'align' => 'left',
    'nowrap' => false,
    'muted' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<td class="px-3 py-2 <?php echo e($muted ? 'text-slate-500 dark:text-slate-400' : 'text-slate-700 dark:text-slate-300'); ?> text-<?php echo e($align); ?> <?php echo e($nowrap ? 'whitespace-nowrap' : ''); ?> <?php echo e($class); ?>">
    <?php echo e($slot); ?>

</td>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\td.blade.php ENDPATH**/ ?>