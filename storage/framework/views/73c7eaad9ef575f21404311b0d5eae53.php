<?php $__env->startSection('title', 'Detail Pengajuan Perubahan Pendidikan - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pelajar.pengajuan-pendidikan.index')).'','label' => 'Kembali']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pelajar.pengajuan-pendidikan.index')).'','label' => 'Kembali']); ?>
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
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Pengajuan Perubahan Pendidikan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Pengajuan Perubahan Pendidikan']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'max-w-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'max-w-xl']); ?>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Tanggal Pengajuan</span>
                <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->created_at->format('d/m/Y H:i')); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jenis</span>
                <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->jenis === 'kenaikan_kelas' ? 'Kenaikan Kelas' : 'Perpindahan Jenjang'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jalur Tujuan</span>
                <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->jalur?->nama ?? '-'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jenjang Tujuan</span>
                <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->jenjang?->nama ?? '-'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Tingkat Tujuan</span>
                <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->tingkat?->nama ?? '-'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.pengajuan.status')); ?></span>
                <?php
                    $map = [
                        'pending' => ['variant' => 'pending', 'label' => __('siswa.pengajuan.menunggu')],
                        'under_review' => ['variant' => 'info', 'label' => __('siswa.pengajuan.sedang_ditinjau')],
                        'revision_required' => ['variant' => 'warning', 'label' => __('siswa.pengajuan.perlu_revisi')],
                        'approved' => ['variant' => 'success', 'label' => __('siswa.pengajuan.disetujui')],
                        'rejected' => ['variant' => 'rejected', 'label' => __('siswa.pengajuan.ditolak')],
                    ];
                    $m = $map[$pengajuan->status] ?? ['variant' => 'default', 'label' => ucfirst($pengajuan->status)];
                ?>
                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $m['variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m['variant'])]); ?><?php echo e($m['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
            </div>
            <?php if($pengajuan->reviewed_at): ?>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Ditinjau Pada</span>
                    <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->reviewed_at->format('d/m/Y H:i')); ?></span>
                </div>
            <?php endif; ?>
            <?php if($pengajuan->reviewedBy): ?>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Ditinjau Oleh</span>
                    <span class="text-slate-900 dark:text-white"><?php echo e($pengajuan->reviewedBy->name); ?></span>
                </div>
            <?php endif; ?>
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Alasan Pengajuan</p>
                <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap"><?php echo e($pengajuan->catatan_operator); ?></p>
            </div>
            <?php if($pengajuan->catatan_operator && in_array($pengajuan->status, ['revision_required', 'rejected'])): ?>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Operator</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap"><?php echo e($pengajuan->catatan_operator); ?></p>
                </div>
            <?php endif; ?>
        </div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\siswa\pengajuan-pendidikan\show.blade.php ENDPATH**/ ?>