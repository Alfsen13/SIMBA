<?php $title = $kuis->judul; ?>



<?php $__env->startSection('content'); ?>
<div class="py-6">
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pelajar.kuis.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pelajar.kuis.index')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01)): ?>
<?php $attributes = $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01; ?>
<?php unset($__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01)): ?>
<?php $component = $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01; ?>
<?php unset($__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01); ?>
<?php endif; ?>

    <h1 class="text-xl font-bold mt-2"><?php echo e($kuis->judul); ?></h1>
    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.shared.durasi')); ?>: <?php echo e($kuis->durasi_menit); ?> <?php echo e(__('siswa.shared.menit')); ?></p>

    <div
        x-data="{ timeLeft: <?php echo e($kuis->durasi_menit * 60); ?>, timer: null }"
        x-init="timer = setInterval(() => { timeLeft--; if(timeLeft <= 0) { clearInterval(timer); $el.closest('form').submit(); } }, 1000)"
        class="mt-4"
    >
        <p class="text-sm font-medium">
            <?php echo e(__('siswa.kuis.sisa_waktu')); ?>

            <span x-text="Math.floor(timeLeft / 60) + ':' + String(timeLeft % 60).padStart(2, '0')"></span>
        </p>

        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => 'true','class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'true','class' => 'mt-4']); ?>
            <form method="POST" action="<?php echo e(route('pelajar.kuis.submit', $kuis)); ?>">
                <?php echo csrf_field(); ?>
                <?php $__currentLoopData = $kuis->soal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $opsi = $s['opsi'] ?? null;
                        if (!is_array($opsi) || count($opsi) < 2) {
                            $opsi = is_array($s['pilihan'] ?? null) ? $s['pilihan'] : [];
                            $opsi = array_values($opsi);
                        }
                        $bloom = strtoupper(trim((string) ($s['bloom'] ?? '')));
                    ?>
                    <div class="mb-4 p-3 border rounded">
                        <div class="flex items-start gap-2 mb-2">
                            <p class="font-medium flex-1"><?php echo e($i + 1); ?>. <?php if (isset($component)) { $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.math','data' => ['value' => $s['pertanyaan'] ?? __('siswa.shared.soal_fallback')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('math'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s['pertanyaan'] ?? __('siswa.shared.soal_fallback'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $attributes = $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $component = $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?></p>
                            <?php if(in_array($bloom, ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], true)): ?>
                                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <?php echo e($bloom); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                        <?php $__currentLoopData = $opsi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="block ml-4">
                                <input
                                    type="radio"
                                    name="jawaban[<?php echo e($i); ?>]"
                                    value="<?php echo e($key); ?>"
                                    required
                                >
                                <span class="ml-1"><?php if (isset($component)) { $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.math','data' => ['value' => $val]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('math'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($val)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $attributes = $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $component = $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm']); ?>
                    <?php echo e(__('siswa.shared.kumpulkan_jawaban')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal490cf816fa75abeeb835565b23a9eae8)): ?>
<?php $attributes = $__attributesOriginal490cf816fa75abeeb835565b23a9eae8; ?>
<?php unset($__attributesOriginal490cf816fa75abeeb835565b23a9eae8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal490cf816fa75abeeb835565b23a9eae8)): ?>
<?php $component = $__componentOriginal490cf816fa75abeeb835565b23a9eae8; ?>
<?php unset($__componentOriginal490cf816fa75abeeb835565b23a9eae8); ?>
<?php endif; ?>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\siswa\kuis\show.blade.php ENDPATH**/ ?>