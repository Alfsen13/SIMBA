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
    x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen antialiased"
>
    <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="min-h-screen flex flex-col transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : ''">
        <?php echo $__env->make('components.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex-1 p-3 lg:p-6">
            <?php echo $__env->make('components.alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>

        <footer class="text-center text-xs text-slate-500 dark:text-slate-400 py-3 border-t border-slate-100 dark:border-slate-800 mt-auto">
            &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name', 'SIMBA')); ?>. <?php echo e(__('common.all_rights_reserved')); ?>

        </footer>
    </main>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\layouts\app.blade.php ENDPATH**/ ?>