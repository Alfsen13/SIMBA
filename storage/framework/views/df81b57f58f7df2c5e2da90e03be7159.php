<?php $__env->startSection('title', __('navigation.quizzes') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('guru_soal.daftar_kuis'),'subtitle' => __('guru_soal.subtitle_daftar_kuis')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.daftar_kuis')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.subtitle_daftar_kuis'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('guru_soal.judul')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('guru_soal.kelas')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('guru_soal.pertemuan')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('guru_soal.durasi')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('common.active')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('guru_soal.peserta')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal01458c31083fa607d5bca4115c7452df = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01458c31083fa607d5bca4115c7452df = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('common.actions')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $attributes = $__attributesOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__attributesOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01458c31083fa607d5bca4115c7452df)): ?>
<?php $component = $__componentOriginal01458c31083fa607d5bca4115c7452df; ?>
<?php unset($__componentOriginal01458c31083fa607d5bca4115c7452df); ?>
<?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php $__empty_1 = true; $__currentLoopData = $kuisList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kuis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if (isset($component)) { $__componentOriginal12c94140e7bbf6927428c65e570fe77d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal12c94140e7bbf6927428c65e570fe77d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tr','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => ['class' => 'font-medium text-slate-900 dark:text-slate-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'font-medium text-slate-900 dark:text-slate-100']); ?><?php echo e($kuis->judul); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($kuis->kelas?->nama_kelas ?? '-'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($kuis->pertemuan?->nama_pertemuan ?? '-'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('guru_soal.durasi_menit', ['menit' => $kuis->durasi_menit])); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $kuis->is_aktif ? 'verified' : 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kuis->is_aktif ? 'verified' : 'danger')]); ?>
                                <?php echo e($kuis->is_aktif ? __('common.active') : __('guru_soal.nonaktif')); ?>

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
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <span class="text-sm <?php echo e($kuis->hasil_count > 0 ? 'text-green-800 dark:text-green-400 font-medium' : 'text-slate-500 dark:text-slate-400'); ?>">
                                <?php echo e(__('guru_soal.jumlah_siswa', ['jumlah' => $kuis->hasil_count])); ?>

                            </span>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <div class="flex items-center gap-2">
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
                                    <?php echo e(__('guru_soal.edit')); ?>

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
                                    <button
                                        type="submit"
                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-800"
                                    >
                                        <?php echo e(__('common.delete')); ?>

                                    </button>
                                </form>
                            </div>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal12c94140e7bbf6927428c65e570fe77d)): ?>
<?php $attributes = $__attributesOriginal12c94140e7bbf6927428c65e570fe77d; ?>
<?php unset($__attributesOriginal12c94140e7bbf6927428c65e570fe77d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal12c94140e7bbf6927428c65e570fe77d)): ?>
<?php $component = $__componentOriginal12c94140e7bbf6927428c65e570fe77d; ?>
<?php unset($__componentOriginal12c94140e7bbf6927428c65e570fe77d); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php if (isset($component)) { $__componentOriginal12c94140e7bbf6927428c65e570fe77d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal12c94140e7bbf6927428c65e570fe77d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tr','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => ['align' => 'center','muted' => true,'colspan' => '7','class' => 'py-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'center','muted' => true,'colspan' => '7','class' => 'py-6']); ?><?php echo e(__('guru_soal.kosong_kuis')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $attributes = $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c)): ?>
<?php $component = $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c; ?>
<?php unset($__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal12c94140e7bbf6927428c65e570fe77d)): ?>
<?php $attributes = $__attributesOriginal12c94140e7bbf6927428c65e570fe77d; ?>
<?php unset($__attributesOriginal12c94140e7bbf6927428c65e570fe77d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal12c94140e7bbf6927428c65e570fe77d)): ?>
<?php $component = $__componentOriginal12c94140e7bbf6927428c65e570fe77d; ?>
<?php unset($__componentOriginal12c94140e7bbf6927428c65e570fe77d); ?>
<?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800"><?php echo e($kuisList->links()); ?></div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\kuis\index.blade.php ENDPATH**/ ?>