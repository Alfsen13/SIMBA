<?php $__env->startSection('title', __('auth.login_button') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="text-center mb-5">
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                <?php echo e(__('auth.login_title')); ?>

            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?php echo e(__('auth.login_subtitle')); ?>

            </p>
        </div>

        <form
            action="<?php echo e(route('login')); ?>"
            method="POST"
            class="space-y-4"
        >
            <?php echo csrf_field(); ?>

            <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'email','type' => 'email','label' => 'Email','value' => ''.e(old('email')).'','placeholder' => 'nama@email.com','required' => true,'icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','type' => 'email','label' => 'Email','value' => ''.e(old('email')).'','placeholder' => 'nama@email.com','required' => true,'icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14)): ?>
<?php $attributes = $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14; ?>
<?php unset($__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14)): ?>
<?php $component = $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14; ?>
<?php unset($__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-password','data' => ['name' => 'password','label' => __('auth.password_label'),'placeholder' => __('auth.password_placeholder'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-password'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'password','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.password_label')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.password_placeholder')),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6)): ?>
<?php $attributes = $__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6; ?>
<?php unset($__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6)): ?>
<?php $component = $__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6; ?>
<?php unset($__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6); ?>
<?php endif; ?>

            <label class="flex items-center gap-2 cursor-pointer w-fit">
                <input
                    type="checkbox"
                    name="remember"
                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500/20 dark:bg-slate-800"
                >
                <span class="text-sm text-slate-600 dark:text-slate-400"><?php echo e(__('auth.remember')); ?></span>
            </label>

            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','class' => 'w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'w-full']); ?>
                <?php echo e(__('auth.login_button')); ?>

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
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
        </form>

        <div class="border-t border-slate-200 dark:border-slate-800 my-5"></div>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            <?php echo e(__('auth.no_account')); ?>

            <a
                href="<?php echo e(route('register')); ?>"
                class="font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition"
            >
                <?php echo e(__('auth.register_link')); ?>

            </a>
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /app/resources/views/auth/login.blade.php ENDPATH**/ ?>