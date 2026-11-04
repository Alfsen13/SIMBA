<?php $title = $pertemuan->nama_pertemuan; ?>



<?php $__env->startSection('content'); ?>
<div class="py-6">
    <?php if (isset($component)) { $__componentOriginalaa240bda8b356b6b0d703079c850dd3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa240bda8b356b6b0d703079c850dd3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.aktivitas-timer','data' => ['log' => $aktivitasLog]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('aktivitas-timer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['log' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($aktivitasLog)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa240bda8b356b6b0d703079c850dd3d)): ?>
<?php $attributes = $__attributesOriginalaa240bda8b356b6b0d703079c850dd3d; ?>
<?php unset($__attributesOriginalaa240bda8b356b6b0d703079c850dd3d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa240bda8b356b6b0d703079c850dd3d)): ?>
<?php $component = $__componentOriginalaa240bda8b356b6b0d703079c850dd3d; ?>
<?php unset($__componentOriginalaa240bda8b356b6b0d703079c850dd3d); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pelajar.kelas.show', $pertemuan->kelas_id)).'','label' => __('siswa.kelas.kembali_ke_kelas')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pelajar.kelas.show', $pertemuan->kelas_id)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('siswa.kelas.kembali_ke_kelas'))]); ?>
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

    <div class="mt-2">
        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide"><?php echo e(__('siswa.shared.pertemuan')); ?> <?php echo e($pertemuan->urutan); ?></span>
        <h1 class="text-xl font-bold mt-0.5"><?php echo e($pertemuan->nama_pertemuan); ?></h1>
        <?php if($pertemuan->deskripsi): ?>
            <p class="text-slate-500 dark:text-slate-400 mt-1"><?php echo e($pertemuan->deskripsi); ?></p>
        <?php endif; ?>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => 'true','class' => 'border-l-4 border-emerald-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'true','class' => 'border-l-4 border-emerald-500']); ?>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('siswa.shared.materi')); ?></h2>
                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-600/10 px-2 py-0.5 rounded-full"><?php echo e($pertemuan->materi->count()); ?></span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $pertemuan->materi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a
                    href="<?php echo e(route('pelajar.materi.show', $m)); ?>"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-600/10 transition-colors"
                >
                    <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300"><?php echo e($m->judul); ?></p>
                    <?php if($m->bab): ?>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.shared.bab', ['bab' => $m->bab])); ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4"><?php echo e(__('siswa.materi.belum_ada')); ?></p>
            <?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => 'true','class' => 'border-l-4 border-purple-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'true','class' => 'border-l-4 border-purple-500']); ?>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('siswa.shared.ai_tutor')); ?></h2>
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-500/10 px-2 py-0.5 rounded-full"><?php echo e($pertemuan->materi->count()); ?></span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $pertemuan->materi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a
                    href="<?php echo e(route('pelajar.ai-tutor.chat', [$pertemuan->kelas, $pertemuan])); ?>"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-purple-50 transition-colors"
                >
                    <p class="text-sm font-medium text-purple-700 dark:text-purple-300"><?php echo e($m->judul); ?></p>
                    <?php if($m->bab): ?>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.shared.bab', ['bab' => $m->bab])); ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4"><?php echo e(__('siswa.materi.belum_ada')); ?></p>
            <?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => 'true','class' => 'border-l-4 border-emerald-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'true','class' => 'border-l-4 border-emerald-500']); ?>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('navigation.assignments')); ?></h2>
                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-600/10 px-2 py-0.5 rounded-full"><?php echo e($pertemuan->tugas->count()); ?></span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $pertemuan->tugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a
                    href="<?php echo e(route('pelajar.tugas.show', $t)); ?>"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-emerald-50 transition-colors"
                >
                    <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300"><?php echo e($t->judul); ?></p>
                    <?php if($t->deadline): ?>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.shared.deadline', ['deadline' => $t->deadline->format('d M Y H:i')])); ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4"><?php echo e(__('siswa.tugas.belum_ada')); ?></p>
            <?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => 'true','class' => 'border-l-4 border-amber-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'true','class' => 'border-l-4 border-amber-500']); ?>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('navigation.quizzes')); ?></h2>
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-full"><?php echo e($pertemuan->kuis->count()); ?></span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $pertemuan->kuis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a
                    href="<?php echo e(route('pelajar.kuis.show', $k)); ?>"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-amber-50 transition-colors"
                >
                    <p class="text-sm font-medium text-amber-700 dark:text-amber-300"><?php echo e($k->judul); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($k->durasi_menit); ?> <?php echo e(__('siswa.shared.menit')); ?></p>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4"><?php echo e(__('siswa.kuis.belum_ada')); ?></p>
            <?php endif; ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\siswa\pertemuan\show.blade.php ENDPATH**/ ?>