<?php $__env->startSection('title', 'Pendaftaran Kelas - Operator'); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-6">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('navigation.class_registration'),'subtitle' => __('operator.registration_subtitle')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('navigation.class_registration')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('operator.registration_subtitle'))]); ?>
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

        <?php if($permintaan->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['message' => __('operator.empty_registrations_page')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('operator.empty_registrations_page'))]); ?>
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
                <div class="overflow-x-auto">
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
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('operator.th_student')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('navigation.classes')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['align' => 'left']); ?><?php echo e(__('operator.th_submitted')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.th','data' => ['align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right']); ?><?php echo e(__('common.actions')); ?> <?php echo $__env->renderComponent(); ?>
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
                            <?php $__currentLoopData = $permintaan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                        <div class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($p->siswa_nama); ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($p->siswa_email); ?></div>
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
                                        <div class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($p->nama_kelas); ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            <?php echo e($p->kode_kelas); ?><?php if($p->kategori): ?> &middot; <?php echo e($p->kategori); ?><?php endif; ?>
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
                                    <?php if (isset($component)) { $__componentOriginale4bbc7ac7aa8c098a103fce9117edf6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale4bbc7ac7aa8c098a103fce9117edf6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => ['nowrap' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nowrap' => true]); ?><?php echo e(\Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.td','data' => ['align' => 'right','nowrap' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','nowrap' => true]); ?>
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="<?php echo e(route('operator.pendaftaran-kelas.setujui', ['kelas' => $p->kelas_id, 'siswa' => $p->siswa_id])); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm']); ?><?php echo e(__('operator.approve')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal490cf816fa75abeeb835565b23a9eae8)): ?>
<?php $attributes = $__attributesOriginal490cf816fa75abeeb835565b23a9eae8; ?>
<?php unset($__attributesOriginal490cf816fa75abeeb835565b23a9eae8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal490cf816fa75abeeb835565b23a9eae8)): ?>
<?php $component = $__componentOriginal490cf816fa75abeeb835565b23a9eae8; ?>
<?php unset($__componentOriginal490cf816fa75abeeb835565b23a9eae8); ?>
<?php endif; ?>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('operator.pendaftaran-kelas.tolak', ['kelas' => $p->kelas_id, 'siswa' => $p->siswa_id])); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'submit','size' => 'sm','color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm','color' => 'red']); ?><?php echo e(__('operator.title_reject')); ?> <?php echo $__env->renderComponent(); ?>
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
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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
            <div class="mt-4"><?php echo e($permintaan->links()); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\operator\pendaftaran-kelas\index.blade.php ENDPATH**/ ?>