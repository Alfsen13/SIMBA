<?php $__env->startSection('title', $materi->judul . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e($materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id)).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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

    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4']); ?>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100"><?php echo e($materi->judul); ?></h1>
        <dl class="mt-4 space-y-3">
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.kelas')); ?></dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100"><?php echo e($materi->kelas?->nama_kelas ?? '-'); ?></dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.bab')); ?></dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100"><?php echo e($materi->bab ?? '-'); ?></dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.file')); ?></dt>
                <dd class="text-sm">
                    <?php if($materi->file_path): ?>
                        <div class="flex items-center gap-3">
                            <a
                                href="<?php echo e(Storage::url($materi->file_path)); ?>"
                                class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                target="_blank"
                            >
                                <?php echo e(__('guru_kelas.materi.lihat')); ?>

                            </a>
                            <a
                                href="<?php echo e(Storage::url($materi->file_path)); ?>"
                                class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                download
                            >
                                <?php echo e(__('guru_kelas.download')); ?>

                            </a>
                        </div>
                    <?php else: ?>
                        <span class="text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.tidak_ada_file')); ?></span>
                    <?php endif; ?>
                </dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.diunggah_oleh')); ?></dt>
                <dd class="text-sm">
                    <?php echo e($materi->guru?->name ?? '-'); ?>

                </dd>
            </div>
        </dl>

        <?php if($materi->file_path): ?>
            <?php $tipeFile = strtolower($materi->file_type ?? ''); ?>
            <?php if($tipeFile === 'pdf'): ?>
                <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 75vh;">
                    <iframe src="<?php echo e(Storage::url($materi->file_path)); ?>" class="w-full h-full border-0"></iframe>
                </div>
            <?php elseif(in_array($tipeFile, ['png', 'jpg', 'jpeg', 'gif', 'webp'])): ?>
                <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                    <img src="<?php echo e(Storage::url($materi->file_path)); ?>" alt="<?php echo e($materi->judul); ?>" class="max-w-full mx-auto rounded-lg">
                </div>
            <?php else: ?>
                <p class="mt-6 text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.pratinjau_tidak_tersedia')); ?> (<?php echo e(strtoupper($tipeFile ?: '?')); ?>)</p>
            <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\materi\show.blade.php ENDPATH**/ ?>