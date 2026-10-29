<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'submit',
    'href' => null,
    'size' => 'md',
    'disabled' => false,
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
    'type' => 'submit',
    'href' => null,
    'size' => 'md',
    'disabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $base = 'inline-flex items-center gap-1.5 font-medium rounded-lg transition-colors duration-150';
    $gradient = 'text-white bg-emerald-700 hover:bg-emerald-800 shadow-sm';
    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-sm font-semibold',
    ];
    $classes = $base . ' ' . $gradient . ' ' . ($sizes[$size] ?? $sizes['md']);
    if ($disabled) $classes .= ' opacity-50 cursor-not-allowed';
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->class($classes)); ?>>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php if($disabled): ?> disabled <?php endif; ?> <?php echo e($attributes->class($classes)); ?>>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views/components/btn-primary.blade.php ENDPATH**/ ?>