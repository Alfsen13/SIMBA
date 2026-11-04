<?php $title = $materi->judul; ?>



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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e($materi->pertemuan_id ? route('pelajar.pertemuan.show', $materi->pertemuan_id) : route('pelajar.kelas.show', $materi->kelas_id)).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($materi->pertemuan_id ? route('pelajar.pertemuan.show', $materi->pertemuan_id) : route('pelajar.kelas.show', $materi->kelas_id)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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

    <h1 class="text-xl font-bold mt-2"><?php echo e($materi->judul); ?></h1>
    <?php if($materi->bab): ?>
        <p class="text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.shared.bab', ['bab' => $materi->bab])); ?></p>
    <?php endif; ?>
    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.kelas.guru', ['guru' => $materi->guru?->name])); ?></p>

    <?php if($materi->file_path): ?>
        <div class="mt-6 flex items-center gap-3">
            <a
                href="<?php echo e(route('pelajar.materi.preview', $materi)); ?>"
                target="_blank"
                class="inline-flex items-center gap-2 bg-emerald-700 text-white px-4 py-2 rounded-lg hover:bg-emerald-800 transition-colors text-sm font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <?php echo e(__('siswa.materi.lihat')); ?>

            </a>
            <a
                href="<?php echo e(route('pelajar.materi.download', $materi)); ?>"
                class="inline-flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                <?php echo e(__('common.download')); ?>

            </a>
        </div>

        <?php if(in_array(strtolower($materi->file_type ?? ''), ['pdf'])): ?>
            <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 75vh;">
                <iframe
                    src="<?php echo e(route('pelajar.materi.preview', $materi)); ?>"
                    class="w-full h-full border-0"
                ></iframe>
            </div>
        <?php elseif(in_array(strtolower($materi->file_type ?? ''), ['png', 'jpg', 'jpeg', 'gif', 'webp'])): ?>
            <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                <img
                    src="<?php echo e(route('pelajar.materi.preview', $materi)); ?>"
                    alt="<?php echo e($materi->judul); ?>"
                    class="max-w-full mx-auto rounded-lg"
                />
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.materi.filetype_prefix')); ?> <span class="font-mono"><?php echo e(strtoupper($materi->file_type ?? '?')); ?></span> <?php echo e(__('siswa.materi.filetype_suffix')); ?></p>
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
        <?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
            <p class="text-slate-500 dark:text-slate-400"><?php echo e(__('siswa.materi.tidak_ada_file')); ?></p>
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
    <?php endif; ?>

    <?php if($chunks->isNotEmpty()): ?>
        <div class="mt-4" id="kutipan-section" x-data="{ open: true }">
            <div class="flex items-center justify-between gap-3 mb-3">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200">
                    <svg class="w-5 h-5 inline text-emerald-700 dark:text-emerald-400 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    <?php echo e(__('siswa.materi.kutipan_ai_tutor')); ?>

                    <span class="ml-1 text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full align-middle"><?php echo e($chunks->count()); ?></span>
                </h2>
                <button
                    type="button"
                    @click="open = !open"
                    class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-controls="kutipan-list"
                >
                    <span x-show="open"><?php echo e(__('siswa.materi.sembunyikan_kutipan')); ?></span>
                    <span x-show="!open"><?php echo e(__('siswa.materi.tampilkan_kutipan')); ?></span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
            <div id="kutipan-list" x-show="open" x-transition class="space-y-3">
                <?php $__currentLoopData = $chunks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isHighlighted = $highlight && stripos($chunk->content, $highlight) !== false;
                    ?>
                    <div
                        id="chunk-<?php echo e($chunk->id); ?>"
                        class="rounded-lg p-4 text-sm leading-relaxed transition-all duration-500
                            <?php echo e($isHighlighted
                                ? 'bg-yellow-50 dark:bg-yellow-500/10 border-2 border-yellow-400 dark:border-yellow-700 shadow-md ring-2 ring-yellow-200 dark:ring-yellow-700/40'
                                : 'bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800'); ?>"
                    >
                        <span class="text-xs font-semibold <?php echo e($isHighlighted ? 'text-yellow-700 dark:text-yellow-300' : 'text-slate-500 dark:text-slate-400'); ?>">
                            <?php echo e(__('siswa.shared.bagian')); ?> <?php echo e($chunk->chunk_index + 1); ?>

                        </span>
                        <p class="mt-1 text-slate-700 dark:text-slate-300 whitespace-pre-line"><?php echo nl2br(e($chunk->content)); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if($highlight): ?>
<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('kutipan-section');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\siswa\materi\show.blade.php ENDPATH**/ ?>