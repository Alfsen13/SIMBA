<?php $title = $kelas->nama_kelas; ?>



<?php $__env->startSection('content'); ?>
<div class="py-6">
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pelajar.kelas.index')).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pelajar.kelas.index')).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold"><?php echo e($kelas->nama_kelas); ?></h1>
            <?php if($kelas->kategori): ?><p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.kelas.kategori', ['kategori' => $kelas->kategori])); ?></p><?php endif; ?>
            <p class="text-slate-500 dark:text-slate-400"><?php echo e($kelas->deskripsi); ?></p>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.kelas.guru', ['guru' => $kelas->guru?->name])); ?></p>
        </div>
        <form
            action="<?php echo e(route('pelajar.kelas.unenroll', $kelas)); ?>"
            method="POST"
            x-data
            x-on:submit.prevent="confirm('<?php echo e(__('siswa.kelas.konfirmasi_keluar')); ?>') && $el.submit()"
        >
            <?php echo csrf_field(); ?>
            <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'submit','color' => 'red','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'red','size' => 'sm']); ?>
                <?php echo e(__('siswa.kelas.keluar')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1edae8760811c7b935175a7435923573)): ?>
<?php $attributes = $__attributesOriginal1edae8760811c7b935175a7435923573; ?>
<?php unset($__attributesOriginal1edae8760811c7b935175a7435923573); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1edae8760811c7b935175a7435923573)): ?>
<?php $component = $__componentOriginal1edae8760811c7b935175a7435923573; ?>
<?php unset($__componentOriginal1edae8760811c7b935175a7435923573); ?>
<?php endif; ?>
        </form>
    </div>

    <h2 class="text-lg font-semibold mt-4 mb-4"><?php echo e(__('siswa.kelas.daftar_pertemuan')); ?></h2>

    <div class="space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $kelas->pertemuan->sortBy('urutan'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a
                href="<?php echo e(route('pelajar.pertemuan.show', $p)); ?>"
                class="block p-4 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-emerald-300 hover:shadow-sm transition-all"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide"><?php echo e(__('siswa.shared.pertemuan')); ?> <?php echo e($p->urutan); ?></span>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-0.5"><?php echo e($p->nama_pertemuan); ?></h3>
                        <?php if($p->deskripsi): ?>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e(Str::limit($p->deskripsi, 120)); ?></p>
                        <?php endif; ?>
                    </div>
                    <svg class="w-5 h-5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-slate-500 dark:text-slate-400 text-center py-6"><?php echo e(__('siswa.kelas.belum_ada_pertemuan')); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\siswa\kelas\show.blade.php ENDPATH**/ ?>