<?php $__env->startSection('title', $pertemuan->nama_pertemuan . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pengajar.kelas.show', $pertemuan->kelas)).'','label' => __('guru_kelas.pertemuan.kembali_ke_kelas')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.kelas.show', $pertemuan->kelas)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.pertemuan.kembali_ke_kelas'))]); ?>
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
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100"><?php echo e(ucfirst(__('guru_kelas.kata.pertemuan'))); ?> <?php echo e($pertemuan->urutan); ?>: <?php echo e($pertemuan->nama_pertemuan); ?></h1>
                    <?php if($pertemuan->dibuka): ?>
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300"><?php echo e(__('guru_kelas.terbuka')); ?></span>
                    <?php else: ?>
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.terkunci')); ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?php echo e($pertemuan->kelas->nama_kelas); ?></p>
            </div>
            <div class="flex items-center gap-2">
                <form
                    action="<?php echo e($pertemuan->dibuka ? route('pengajar.pertemuan.kunci', $pertemuan) : route('pengajar.pertemuan.buka', $pertemuan)); ?>"
                    method="POST"
                >
                    <?php echo csrf_field(); ?>
                    <button class="text-sm px-3 py-1.5 rounded-lg font-medium border transition <?php echo e($pertemuan->dibuka ? 'border-amber-300 text-amber-700 dark:text-amber-400 hover:bg-amber-50' : 'border-emerald-300 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50'); ?>">
                        <?php echo e($pertemuan->dibuka ? __('guru_kelas.pertemuan.kunci_pertemuan') : __('guru_kelas.pertemuan.buka_pertemuan')); ?>

                    </button>
                </form>
                <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pengajar.pertemuan.edit', $pertemuan)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.pertemuan.edit', $pertemuan)).'','size' => 'sm']); ?>
                    <?php echo e(__('guru_kelas.edit')); ?>

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
            </div>
        </div>
        <?php if($pertemuan->deskripsi): ?>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3"><?php echo e($pertemuan->deskripsi); ?></p>
        <?php endif; ?>
        <?php if($pertemuan->tanggal_mulai || $pertemuan->tanggal_selesai): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">
                <?php echo e(__('guru_kelas.pertemuan.jadwal')); ?>

                <?php echo e($pertemuan->tanggal_mulai?->format('d M Y') ?? '-'); ?>

                &ndash;
                <?php echo e($pertemuan->tanggal_selesai?->format('d M Y') ?? '-'); ?>

            </p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('guru_kelas.kehadiran.title')); ?></h2>
            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['href' => ''.e(route('pengajar.kehadiran.edit', $pertemuan)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.kehadiran.edit', $pertemuan)).'','size' => 'sm']); ?>
                <?php echo e(__('guru_kelas.pertemuan.kelola_kehadiran')); ?>

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
        </div>
        <?php
            $totalKehadiran = $pertemuan->kehadiran->count();
            $perStatus = $pertemuan->kehadiran->countBy('status');
        ?>
        <?php if($totalKehadiran > 0): ?>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                <?php echo e(__('guru_kelas.kehadiran.tercatat')); ?> <strong><?php echo e($totalKehadiran); ?></strong> <?php echo e(__('guru_kelas.kata.siswa')); ?> &mdash;
                <?php echo e(__('guru_kelas.kehadiran.hadir')); ?>: <strong><?php echo e($perStatus->get('hadir', 0)); ?></strong>,
                <?php echo e(__('guru_kelas.kehadiran.izin')); ?>: <strong><?php echo e($perStatus->get('izin', 0)); ?></strong>,
                <?php echo e(__('guru_kelas.kehadiran.sakit')); ?>: <strong><?php echo e($perStatus->get('sakit', 0)); ?></strong>,
                <?php echo e(__('guru_kelas.kehadiran.alpha')); ?>: <strong><?php echo e($perStatus->get('alpha', 0)); ?></strong>.
            </p>
        <?php else: ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.pertemuan.belum_ada_absensi')); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e(ucfirst(__('guru_kelas.kata.materi'))); ?></h2>
            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['href' => ''.e(route('pengajar.materi.create', ['pertemuan_id' => $pertemuan->id])).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.materi.create', ['pertemuan_id' => $pertemuan->id])).'','size' => 'sm']); ?>
                + <?php echo e(__('guru_kelas.materi.tambah')); ?>

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
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pertemuan->materi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $materi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($materi->judul); ?></span>
                        <?php if($materi->bab): ?>
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2"><?php echo e(__('guru_kelas.bab_label', ['bab' => $materi->bab])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex gap-2">
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pengajar.materi.show', $materi)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.materi.show', $materi)).'','size' => 'sm']); ?>
                        <?php echo e(__('common.detail')); ?>

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
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pengajar.materi.edit', $materi)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.materi.edit', $materi)).'','size' => 'sm']); ?>
                        <?php echo e(__('guru_kelas.edit')); ?>

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
                    <form
                        action="<?php echo e(route('pengajar.materi.destroy', $materi)); ?>"
                        method="POST"
                        x-data
                        x-on:submit.prevent="confirm('<?php echo e(__('guru_kelas.materi.konfirmasi_hapus')); ?>') && $el.submit()"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
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
                            <?php echo e(__('common.delete')); ?>

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
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.pertemuan.belum_ada_materi')); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6 border-l-4 border-purple-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6 border-l-4 border-purple-500']); ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">AI Tutor</h2>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pertemuan->materi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $materi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($materi->judul); ?></span>
                        <?php if($materi->bab): ?>
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2"><?php echo e(__('guru_kelas.bab_label', ['bab' => $materi->bab])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pelajar.ai-tutor.chat', [$pertemuan->kelas, $pertemuan])).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pelajar.ai-tutor.chat', [$pertemuan->kelas, $pertemuan])).'','size' => 'sm']); ?>
                        <?php echo e(__('guru_kelas.pertemuan.buka_ai_tutor')); ?>

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
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.pertemuan.belum_ada_ai_tutor')); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e(ucfirst(__('guru_kelas.kata.tugas'))); ?></h2>
            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['href' => ''.e(route('pengajar.tugas.create', ['pertemuan_id' => $pertemuan->id])).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.tugas.create', ['pertemuan_id' => $pertemuan->id])).'','size' => 'sm']); ?>
                + <?php echo e(__('guru_kelas.tambah_tugas')); ?>

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
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pertemuan->tugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tugas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($tugas->judul); ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 ml-2"><?php echo e(match($tugas->tipe) { 'upload' => __('guru_kelas.tugas_tipe.upload'), 'pilihan_ganda' => __('guru_kelas.tugas_tipe.pilihan_ganda'), 'essai' => __('guru_kelas.tugas_tipe.essai'), default => ucfirst($tugas->tipe) }); ?></span>
                        <?php if($tugas->deadline): ?>
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2"><?php echo e(__('guru_kelas.deadline', ['waktu' => $tugas->deadline->format('d M Y H:i')])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex gap-2">
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pengajar.tugas.show', $tugas)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.tugas.show', $tugas)).'','size' => 'sm']); ?>
                        <?php echo e(__('common.detail')); ?>

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
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pengajar.tugas.edit', $tugas)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.tugas.edit', $tugas)).'','size' => 'sm']); ?>
                        <?php echo e(__('guru_kelas.edit')); ?>

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
                    <form
                        action="<?php echo e(route('pengajar.tugas.destroy', $tugas)); ?>"
                        method="POST"
                        x-data
                        x-on:submit.prevent="confirm('<?php echo e(__('guru_soal.konfirmasi_hapus_tugas')); ?>') && $el.submit()"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
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
                            <?php echo e(__('common.delete')); ?>

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
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.pertemuan.belum_ada_tugas')); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e(ucfirst(__('guru_kelas.kata.kuis'))); ?></h2>
            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['href' => ''.e(route('pengajar.kuis.create', ['pertemuan_id' => $pertemuan->id])).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.kuis.create', ['pertemuan_id' => $pertemuan->id])).'','size' => 'sm']); ?>
                + <?php echo e(__('guru_kelas.tambah_kuis')); ?>

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
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pertemuan->kuis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kuis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($kuis->judul); ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 ml-2"><?php echo e($kuis->durasi_menit); ?> <?php echo e(__('guru_kelas.kata.menit')); ?></span>
                        <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $kuis->is_aktif ? 'verified' : 'danger','class' => 'ml-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kuis->is_aktif ? 'verified' : 'danger'),'class' => 'ml-2']); ?>
                            <?php echo e($kuis->is_aktif ? __('common.active') : __('guru_kelas.nonaktif')); ?>

                         <?php echo $__env->renderComponent(); ?>
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
                </div>
                <div class="flex gap-2">
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('pengajar.kuis.edit', $kuis)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.kuis.edit', $kuis)).'','size' => 'sm']); ?>
                        <?php echo e(__('guru_kelas.edit')); ?>

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
                    <form
                        action="<?php echo e(route('pengajar.kuis.destroy', $kuis)); ?>"
                        method="POST"
                        x-data
                        x-on:submit.prevent="confirm('<?php echo e(__('guru_soal.konfirmasi_hapus_kuis')); ?>') && $el.submit()"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
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
                            <?php echo e(__('common.delete')); ?>

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
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.pertemuan.belum_ada_kuis')); ?></p>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\kelas\pertemuan\show.blade.php ENDPATH**/ ?>