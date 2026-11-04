<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<?php if(! empty($items)): ?>
    <ol class="space-y-3">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-3">
                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                    <span class="text-slate-500 dark:text-slate-400"><?php echo e($i + 1); ?>.</span>
                    <?php if (isset($component)) { $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.math','data' => ['value' => $item['soal'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('math'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['soal'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $attributes = $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $component = $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
                </p>

                <?php if(! empty($item['jawaban'])): ?>
                    <div class="mt-2" x-data="{ buka: false }">
                        <button
                            type="button"
                            @click="buka = !buka"
                            :aria-expanded="buka ? 'true' : 'false'"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span x-show="!buka"><?php echo e(__('siswa.remedial.lihat_jawaban')); ?></span>
                            <span x-show="buka" x-cloak><?php echo e(__('siswa.remedial.sembunyikan_jawaban')); ?></span>
                        </button>

                        <div
                            x-show="buka"
                            x-cloak
                            x-transition
                            class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-700"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <?php echo e(__('siswa.remedial.jawaban')); ?>

                            </p>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">
                                <?php if (isset($component)) { $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.math','data' => ['value' => $item['jawaban']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('math'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['jawaban'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $attributes = $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $component = $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\remedial-latihan.blade.php ENDPATH**/ ?>