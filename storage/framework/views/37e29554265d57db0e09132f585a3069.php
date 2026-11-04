<?php $__env->startSection('title', __('guru_kelas.kehadiran.title') . ' - ' . $pertemuan->nama_pertemuan . ' - SIMBA'); ?>

<?php
    $labelStatus = [
        'hadir' => ucfirst(__('guru_kelas.kehadiran.hadir')),
        'izin' => ucfirst(__('guru_kelas.kehadiran.izin')),
        'sakit' => ucfirst(__('guru_kelas.kehadiran.sakit')),
        'alpha' => ucfirst(__('guru_kelas.kehadiran.alpha')),
    ];
    $nilaiAwal = $siswa->mapWithKeys(fn ($s) => [
        $s->id => old('status.' . $s->id, optional($kehadiran->get($s->id))->status ?? 'hadir'),
    ]);
?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pengajar.pertemuan.show', $pertemuan)).'','label' => __('guru_kelas.kehadiran.kembali')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.pertemuan.show', $pertemuan)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.kehadiran.kembali'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('guru_kelas.kehadiran.dengan_nama', ['nama' => $pertemuan->nama_pertemuan]),'subtitle' => __('guru_kelas.kelas_label', ['nama' => $pertemuan->kelas->nama_kelas])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.kehadiran.dengan_nama', ['nama' => $pertemuan->nama_pertemuan])),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.kelas_label', ['nama' => $pertemuan->kelas->nama_kelas]))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['xData' => 'kehadiranForm()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-data' => 'kehadiranForm()']); ?>
        <form
            action="<?php echo e(route('pengajar.kehadiran.update', $pertemuan)); ?>"
            method="POST"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($siswa->count()); ?> <?php echo e(__('guru_kelas.kehadiran.siswa_terdaftar')); ?></p>
                <div class="flex gap-2">
                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'button','size' => 'sm','@click' => 'tandaiSemua(\'hadir\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','size' => 'sm','@click' => 'tandaiSemua(\'hadir\')']); ?>
                        <?php echo e(__('guru_kelas.kehadiran.tandai_hadir')); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'button','size' => 'sm','color' => 'red','@click' => 'tandaiSemua(\'alpha\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','size' => 'sm','color' => 'red','@click' => 'tandaiSemua(\'alpha\')']); ?>
                        <?php echo e(__('guru_kelas.kehadiran.tandai_alpha')); ?>

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

            <?php $__empty_1 = true; $__currentLoopData = $siswa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($h = $kehadiran->get($s->id)); ?>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($s->name); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($s->email); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select
                            name="status[<?php echo e($s->id); ?>]"
                            x-model="status[<?php echo e($s->id); ?>]"
                            class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none"
                        >
                            <?php $__currentLoopData = $labelStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nilai => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($nilai); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input
                            type="text"
                            name="catatan[<?php echo e($s->id); ?>]"
                            value="<?php echo e(old('catatan.' . $s->id, $h->catatan ?? '')); ?>"
                            placeholder="<?php echo e(__('guru_kelas.catatan')); ?>"
                            class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none w-40"
                        >
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.kehadiran.belum_ada_siswa')); ?></p>
            <?php endif; ?>

            <?php if($siswa->isNotEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','class' => 'mt-5 w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'mt-5 w-full']); ?>
                    <?php echo e(__('guru_kelas.kehadiran.simpan')); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function kehadiranForm() {
            return {
                status: <?php echo json_encode($nilaiAwal, 15, 512) ?>,
                tandaiSemua(nilai) {
                    Object.keys(this.status).forEach((id) => {
                        this.status[id] = nilai;
                    });
                },
            };
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\kehadiran\edit.blade.php ENDPATH**/ ?>