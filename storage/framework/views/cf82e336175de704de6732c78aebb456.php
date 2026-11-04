<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'SIMBA'); ?></title>
    <?php echo $__env->make('components.theme-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body
    class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-slate-100 dark:bg-slate-950 antialiased"
>
    
    <div class="fixed top-0 inset-x-0 h-1 bg-emerald-700 dark:bg-emerald-500 z-30 pointer-events-none" aria-hidden="true"></div>

    <div class="fixed top-4 right-4 z-20 flex items-center gap-1">
        <?php if (isset($component)) { $__componentOriginal2090438866f3dcdb76cd8b070bcc302d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-toggle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('theme-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $attributes = $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $component = $__componentOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal7b69d71eac2771bb6249ff4d7cc262ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b69d71eac2771bb6249ff4d7cc262ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.locale-switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('locale-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b69d71eac2771bb6249ff4d7cc262ee)): ?>
<?php $attributes = $__attributesOriginal7b69d71eac2771bb6249ff4d7cc262ee; ?>
<?php unset($__attributesOriginal7b69d71eac2771bb6249ff4d7cc262ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b69d71eac2771bb6249ff4d7cc262ee)): ?>
<?php $component = $__componentOriginal7b69d71eac2771bb6249ff4d7cc262ee; ?>
<?php unset($__componentOriginal7b69d71eac2771bb6249ff4d7cc262ee); ?>
<?php endif; ?>
    </div>

    <?php $containerWidth = trim($__env->yieldContent('container_width')); ?>
    <div class="relative w-full <?php echo e($containerWidth !== '' ? $containerWidth : 'max-w-md'); ?> py-8">
        
        <div class="text-center mb-6">
            <a
                href="/"
                class="inline-flex items-center gap-2.5"
            >
                <div class="w-10 h-10 rounded-lg bg-emerald-700 dark:bg-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    S
                </div>
                <span class="font-bold text-xl text-slate-900 dark:text-white">
                    <?php echo e(config('app.name', 'SIMBA')); ?>

                </span>
            </a>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                <?php echo e(__('common.app_tagline')); ?>

            </p>
        </div>

        <?php echo $__env->make('components.alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\layouts\guest.blade.php ENDPATH**/ ?>