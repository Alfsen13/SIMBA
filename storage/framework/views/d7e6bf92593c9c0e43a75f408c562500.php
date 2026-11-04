<?php $__env->startSection('title', __('auth.pending_title') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 text-center">
        <div class="w-14 h-14 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mx-auto mb-4">
            <svg
                class="w-8 h-8 text-amber-600 dark:text-amber-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        </div>

        <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            <?php echo e(__('auth.pending_title')); ?>

        </h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed max-w-xs mx-auto">
            <?php echo e(__('auth.pending_message')); ?>

        </p>

        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3.5 mb-4 border border-amber-200/50 dark:border-amber-700/50">
            <div class="flex items-start gap-3 text-left">
                <svg
                    class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                        <?php echo e(__('auth.pending_verifying')); ?>

                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        <?php echo e(__('auth.pending_contact')); ?>

                    </p>
                </div>
            </div>
        </div>

        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'submit','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'gray']); ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <?php echo e(__('common.logout')); ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\auth\pending.blade.php ENDPATH**/ ?>