
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'cols' => 1,
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
    'cols' => 1,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $basis = match ((int) $cols) {
        2 => 'md:basis-1/2',
        3 => 'md:basis-1/3',
        default => 'md:basis-full',
    };
?>

<div <?php echo e($attributes->merge(['class' => 'flex flex-wrap -mx-3'])); ?>>
    <div class="w-full <?php echo e($basis); ?> px-3">
        <?php echo e($slot); ?>

    </div>
</div><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\form-columns.blade.php ENDPATH**/ ?>