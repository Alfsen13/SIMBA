<div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800 px-3 lg:px-5 py-2 flex items-center justify-between sticky top-0 z-30">
    <button
        @click="sidebarOpen = !sidebarOpen"
        class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 rounded-lg transition-colors focus:outline-none"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
            />
        </svg>
    </button>

        <div class="flex items-center gap-2">
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
        <?php if(auth()->guard()->check()): ?>
            <?php
                $notifCount = $notifCount ?? auth()->user()->notifikasi()->where('is_read', false)->count();
                $role = auth()->user()->role;
                $notifRoute = match ($role) {
                    'pengelola_operator' => 'pengelola-operator.notifikasi',
                    default => $role . '.notifikasi',
                };
                $dashboardRoute = match ($role) {
                    'pengelola_operator' => 'pengelola-operator.dashboard',
                    default => $role . '.dashboard',
                };
            ?>

            <a
                href="<?php echo e(route($notifRoute)); ?>"
                class="relative p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 rounded-lg transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <?php if($notifCount > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-sm shadow-red-200">
                        <?php echo e($notifCount > 9 ? '9+' : $notifCount); ?>

                    </span>
                <?php endif; ?>
            </a>

            <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2.5 pl-1 pr-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-500 dark:to-emerald-600 flex items-center justify-center text-xs font-bold text-emerald-700 dark:text-white shrink-0">
                        <?php echo e(substr(auth()->user()->name, 0, 1)); ?>

                    </div>
                    <span class="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-200"><?php echo e(auth()->user()->name); ?></span>
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 hidden sm:block transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
                    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate"><?php echo e(auth()->user()->name); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate"><?php echo e(auth()->user()->email); ?></p>
                    </div>
                    <div class="py-1">
                        <a href="<?php echo e(route($dashboardRoute)); ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <?php echo e(__('navigation.dashboard')); ?>

                        </a>
                        <a href="<?php echo e(route($notifRoute)); ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <?php echo e(__('navigation.notifications')); ?>

                        </a>
                    </div>
                    <div class="border-t border-slate-100 dark:border-slate-700 py-1">
                        <form action="<?php echo e(route('logout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <?php echo e(__('common.logout')); ?>

                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300">
                <?php echo e(__('common.login')); ?>

            </a>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /app/resources/views/components/topbar.blade.php ENDPATH**/ ?>