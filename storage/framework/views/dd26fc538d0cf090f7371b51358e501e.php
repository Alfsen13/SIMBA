<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => '',
    'label' => '',
    'color' => 'emerald',
    'icon' => null,
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
    'value' => '',
    'label' => '',
    'color' => 'emerald',
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colors = [
        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-600/10', 'text' => 'text-emerald-700 dark:text-emerald-400'],
        'indigo' => ['bg' => 'bg-emerald-50 dark:bg-emerald-600/10', 'text' => 'text-emerald-700 dark:text-emerald-400'],
        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-500/10', 'text' => 'text-blue-600 dark:text-blue-400'],
        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-500/10', 'text' => 'text-purple-600 dark:text-purple-400'],
        'pink' => ['bg' => 'bg-pink-50 dark:bg-pink-500/10', 'text' => 'text-pink-600 dark:text-pink-400'],
        'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-500/10', 'text' => 'text-rose-600 dark:text-rose-400'],
        'cyan' => ['bg' => 'bg-cyan-50 dark:bg-cyan-500/10', 'text' => 'text-cyan-600 dark:text-cyan-400'],
        'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-500/10', 'text' => 'text-teal-600 dark:text-teal-400'],
        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-500/10', 'text' => 'text-orange-600 dark:text-orange-400'],
        'red' => ['bg' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-600 dark:text-red-400'],
        'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-500/10', 'text' => 'text-violet-600 dark:text-violet-400'],
        'green' => ['bg' => 'bg-green-50 dark:bg-green-500/10', 'text' => 'text-green-700 dark:text-green-400'],
        'slate' => ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-300'],
    ];
    $c = $colors[$color] ?? $colors['emerald'];
?>

<div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 p-3.5">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate"><?php echo e($label); ?></p>
            <p class="mt-1 text-2xl font-semibold tabular-nums leading-none text-slate-900 dark:text-white"><?php echo e($value); ?></p>
        </div>
        <?php if($icon): ?>
            <div class="w-9 h-9 <?php echo e($c['bg']); ?> <?php echo e($c['text']); ?> rounded-lg flex items-center justify-center shrink-0">
                <?php echo $icon; ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\stat-card.blade.php ENDPATH**/ ?>