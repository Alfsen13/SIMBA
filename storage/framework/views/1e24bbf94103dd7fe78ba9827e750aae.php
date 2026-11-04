<?php $__env->startSection('title', $beritum->judul . ' - SIMBA'); ?>

<?php $__env->startSection('container_width', 'max-w-3xl'); ?>

<?php $__env->startSection('content'); ?>
    <div class="text-left w-full">
        <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('home')).'','label' => __('publik.kembali_ke_beranda')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('home')).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('publik.kembali_ke_beranda'))]); ?>
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

        <article>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2">
                <?php echo e($beritum->judul); ?>

            </h1>

            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mb-4">
                <span><?php echo e($beritum->published_at?->format('d F Y')); ?></span>
                <span>&middot;</span>
                <span><?php echo e($beritum->user?->name ?? 'Admin'); ?></span>
            </div>

            <?php if($beritum->excerpt): ?>
                <p class="text-base text-slate-600 dark:text-slate-400 mb-4 font-medium">
                    <?php echo e($beritum->excerpt); ?>

                </p>
            <?php endif; ?>

            <div class="prose prose-slate dark:prose-invert max-w-none text-sm leading-relaxed">
                <?php echo nl2br(e($beritum->konten)); ?>

            </div>
        </article>

        <?php if($beritaLainnya->isNotEmpty()): ?>
            <hr class="my-8 border-slate-200 dark:border-slate-800">

            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">
                <?php echo e(__('publik.berita.lainnya')); ?>

            </h2>

            <div class="space-y-3">
                <?php $__currentLoopData = $beritaLainnya; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e(route('berita.show', $item)); ?>"
                        class="block p-3.5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors"
                    >
                        <h3 class="font-medium text-slate-900 dark:text-slate-100">
                            <?php echo e($item->judul); ?>

                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            <?php echo e($item->published_at?->format('d F Y')); ?>

                        </p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\berita-show.blade.php ENDPATH**/ ?>