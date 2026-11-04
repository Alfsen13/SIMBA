<?php $title = 'AI Tutor — ' . $kelas->nama_kelas; ?>



<?php $__env->startSection('content'); ?>
<div class="py-6">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('navigation.ai_tutor'),'subtitle' => __('publik.ai_tutor.subtitle_pilih_pertemuan', ['kelas' => $kelas->nama_kelas])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('navigation.ai_tutor')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('publik.ai_tutor.subtitle_pilih_pertemuan', ['kelas' => $kelas->nama_kelas]))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('pelajar.ai-tutor')); ?>" class="text-sm text-emerald-700 dark:text-emerald-400 hover:underline">&larr; <?php echo e(__('publik.ai_tutor.kembali_daftar_kelas')); ?></a>
         <?php $__env->endSlot(); ?>
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

    <?php if($pertemuans->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['message' => __('publik.ai_tutor.kosong_pertemuan')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('publik.ai_tutor.kosong_pertemuan'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
    <?php else: ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $pertemuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pertemuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="group relative flex gap-4">
                    
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-10 h-10 rounded-full border-2 border-emerald-200 dark:border-emerald-800 bg-white dark:bg-slate-900 flex items-center justify-center text-sm font-bold text-emerald-700 dark:text-emerald-400 group-hover:border-emerald-400 group-hover:bg-emerald-50 transition-colors z-10">
                            <?php echo e($pertemuan->urutan); ?>

                        </div>
                        <?php if(!$loop->last): ?>
                            <div class="w-0.5 flex-1 bg-gradient-to-b from-emerald-200 to-slate-100 mt-1"></div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="flex-1 pb-4">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden group-hover:shadow-md group-hover:border-emerald-100 transition-all duration-200">
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-700 transition-colors">
                                            <?php echo e($pertemuan->nama_pertemuan); ?>

                                        </h3>
                                        <?php if($pertemuan->deskripsi): ?>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 line-clamp-2"><?php echo e($pertemuan->deskripsi); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <a
                                        href="<?php echo e(route('pelajar.ai-tutor.chat', [$kelas, $pertemuan])); ?>"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-700 text-white text-sm font-medium rounded-lg hover:bg-emerald-800 active:scale-[0.97] transition-all shadow-sm"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <?php echo e(__('publik.ai_tutor.mulai_belajar')); ?>

                                    </a>
                                </div>

                                
                                <div class="mt-3 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span><?php echo e($pertemuan->materi->count()); ?> <?php echo e(__('publik.ai_tutor.materi_tersedia')); ?></span>
                                    <?php if($pertemuan->materi->isNotEmpty()): ?>
                                        <span class="text-slate-300">|</span>
                                        <span><?php echo e($pertemuan->materi->filter(fn($m) => $m->document)->count()); ?> <?php echo e(__('publik.ai_tutor.dokumen')); ?></span>
                                    <?php endif; ?>
                                </div>

                                
                                <?php if($pertemuan->materi->isNotEmpty()): ?>
                                    <div x-data="{ expanded: false }" class="mt-3">
                                        <button
                                            @click="expanded = !expanded"
                                            class="flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-700 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            <span x-show="!expanded"><?php echo e(__('publik.ai_tutor.lihat_materi')); ?></span>
                                            <span x-show="expanded" x-cloak><?php echo e(__('publik.ai_tutor.sembunyikan_materi')); ?></span>
                                        </button>
                                        <div x-show="expanded" x-collapse class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                            <?php $__currentLoopData = $pertemuan->materi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $materi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800 hover:border-emerald-100 hover:bg-emerald-50/30 transition-colors">
                                                    <div class="shrink-0 mt-0.5">
                                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-600/20 flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h4 class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate"><?php echo e($materi->judul); ?></h4>
                                                        <?php if($materi->babSummary): ?>
                                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2"><?php echo e(Str::limit($materi->babSummary->ringkasan, 100)); ?></p>
                                                        <?php else: ?>
                                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo e(__('publik.ai_tutor.ringkasan_belum_tersedia')); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\siswa\ai-tutor\pertemuan.blade.php ENDPATH**/ ?>