<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'SIMBA')); ?> — <?php echo e(__('publik.tagline')); ?></title>
    <?php echo $__env->make('components.theme-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body
    class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased overflow-x-hidden"
    x-data="{ mobileNav: false, scrolled: false }"
    @scroll.window="scrolled = window.scrollY > 20"
>

    
    <nav
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
        :class="scrolled
            ? 'bg-white/90 dark:bg-slate-950/90 backdrop-blur-xl shadow-sm border-b border-slate-200/50 dark:border-slate-800/50'
            : 'bg-transparent'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-emerald-500/20 group-hover:shadow-emerald-500/40 transition-shadow">
                        S
                    </div>
                    <span class="font-bold text-lg text-slate-900 dark:text-white">
                        <?php echo e(config('app.name', 'SIMBA')); ?>

                    </span>
                </a>

                <div class="hidden md:flex items-center gap-6">
                    <a
                        href="#tentang"
                        class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <?php echo e(__('publik.nav.tentang')); ?>

                    </a>
                    <a
                        href="#berita"
                        class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <?php echo e(__('publik.nav.berita')); ?>

                    </a>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
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
                    <?php if(auth()->guard()->check()): ?>
                        <a
                            href="<?php echo e(url('/' . auth()->user()->role)); ?>"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 rounded-lg transition shadow-sm"
                        >
                            <?php echo e(__('navigation.dashboard')); ?>

                            <svg class="ml-1.5 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a
                            href="<?php echo e(route('login')); ?>"
                            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition px-2 py-2"
                        >
                            <?php echo e(__('common.login')); ?>

                        </a>
                        <a
                            href="<?php echo e(route('register')); ?>"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 rounded-lg transition shadow-sm"
                        >
                            <?php echo e(__('publik.auth.daftar')); ?>

                        </a>
                    <?php endif; ?>

                    <button
                        @click="mobileNav = !mobileNav"
                        class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        :class="{ 'bg-slate-100 dark:bg-slate-800': mobileNav }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <template x-if="!mobileNav">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </template>
                            <template x-if="mobileNav">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </template>
                        </svg>
                    </button>
                </div>
            </div>

            
            <div
                x-show="mobileNav"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="md:hidden pb-4 pt-1"
            >
                <div class="flex flex-col gap-1">
                    <a
                        href="#tentang"
                        @click="mobileNav = false"
                        class="px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <?php echo e(__('publik.nav.tentang')); ?>

                    </a>
                    <a
                        href="#berita"
                        @click="mobileNav = false"
                        class="px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <?php echo e(__('publik.nav.berita')); ?>

                    </a>
                </div>
            </div>
        </div>
    </nav>

    
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/80 dark:from-slate-950 dark:via-slate-950 dark:to-emerald-950/30"></div>
        <div class="absolute -top-40 -right-40 w-[420px] h-[420px] rounded-full bg-gradient-to-br from-emerald-200/40 to-teal-200/40 dark:from-emerald-800/20 dark:to-teal-800/20 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-[420px] h-[420px] rounded-full bg-gradient-to-tr from-teal-200/30 to-cyan-200/30 dark:from-teal-800/10 dark:to-cyan-800/10 blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05] bg-grid-indigo"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12 sm:pt-28 sm:pb-16">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 mb-4 border border-emerald-200/50 dark:border-emerald-700/50">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <?php echo e(__('publik.hero.badge')); ?>

                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-slate-900 dark:text-white mb-4 leading-[1.15]">
                        <?php echo e(__('publik.hero.title_1')); ?>

                        <?php echo e(__('publik.hero.with')); ?>

                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400">
                            <?php echo e(__('navigation.ai_tutor')); ?>

                        </span>
                    </h1>

                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mb-6 max-w-lg leading-relaxed">
                        <?php echo e(__('publik.hero.desc')); ?>

                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <?php if(auth()->guard()->check()): ?>
                            <a
                                href="<?php echo e(url('/' . auth()->user()->role)); ?>"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 rounded-lg transition-all shadow-sm"
                            >
                                <?php echo e(__('publik.hero.open_dashboard')); ?>

                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <a
                                href="<?php echo e(route('register')); ?>"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 rounded-lg transition-all shadow-sm"
                            >
                                <?php echo e(__('publik.hero.start_now')); ?>

                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="hidden lg:block relative">
                    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl shadow-emerald-500/10 dark:shadow-emerald-900/30 border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="flex items-center gap-1.5 px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <span class="ml-3 text-xs text-slate-500 dark:text-slate-400 font-medium">simba.test</span>
                        </div>

                        <div class="p-4">
                            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-lg p-4 text-white">
                                <div class="text-xs text-emerald-100 font-medium mb-1.5">
                                    <?php echo e(__('publik.hero.mockup_title')); ?>

                                </div>
                                <p class="text-sm leading-relaxed opacity-90">
                                    <?php echo e(__('publik.hero.mockup_quote')); ?>

                                </p>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <?php if($berita->isNotEmpty()): ?>
                                    <?php $__currentLoopData = $berita->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a
                                            href="<?php echo e(route('berita.show', $item)); ?>"
                                            class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                                        >
                                            <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                                    <?php echo e($item->judul); ?>

                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    <?php echo e($item->published_at?->diffForHumans()); ?>

                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <?php if($berita->isNotEmpty()): ?>
        <section id="berita" class="py-12 sm:py-16 relative bg-slate-50 dark:bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-cyan-100 dark:bg-cyan-900/60 text-cyan-700 dark:text-cyan-300 mb-3">
                        <?php echo e(__('publik.berita.badge')); ?>

                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        <?php echo e(__('publik.berita.latest')); ?>

                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $berita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e(route('berita.show', $item)); ?>"
                            class="group bg-white dark:bg-slate-900 rounded-lg p-4 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition-all"
                        >
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">
                                <?php echo e($item->published_at?->format('d F Y')); ?>

                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors mb-1.5">
                                <?php echo e($item->judul); ?>

                            </h3>
                            <?php if($item->excerpt): ?>
                                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    <?php echo e($item->excerpt); ?>

                                </p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    
    <section id="tentang" class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 mb-3">
                    <?php echo e(__('publik.tentang.badge')); ?>

                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
                    <?php echo e(__('publik.tentang.what_is')); ?>

                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400">
                        SIMBA
                    </span>?
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed">
                    <?php echo e(__('publik.tentang.desc')); ?>

                </p>
            </div>
        </div>
    </section>

    
    <footer class="bg-slate-950 dark:bg-black text-slate-400 dark:text-slate-400 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs">
                        S
                    </div>
                    <span class="font-bold text-base text-white">
                        <?php echo e(config('app.name', 'SIMBA')); ?>

                    </span>
                </div>

                <div class="flex items-center gap-5 text-sm">
                    <a href="#tentang" class="hover:text-white transition"><?php echo e(__('publik.nav.tentang')); ?></a>
                    <a href="#berita" class="hover:text-white transition"><?php echo e(__('publik.nav.berita')); ?></a>
                    <?php if(auth()->guard()->check()): ?>
                        <a
                            href="<?php echo e(url('/' . auth()->user()->role)); ?>"
                            class="hover:text-white transition"
                        >
                            <?php echo e(__('navigation.dashboard')); ?>

                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="hover:text-white transition"><?php echo e(__('common.login')); ?></a>
                        <a
                            href="<?php echo e(route('register')); ?>"
                            class="inline-flex items-center px-3.5 py-1.5 text-sm font-medium text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg transition"
                        >
                            <?php echo e(__('publik.auth.daftar')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name', 'SIMBA')); ?>. <?php echo e(__('publik.footer.all_rights_reserved')); ?></p>
                <p class="text-slate-500 dark:text-slate-400">
                    <?php echo e(__('publik.footer.built_with', ['app' => app()->version()])); ?> &middot; Tailwind CSS v4 &middot; Alpine.js
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
<?php /**PATH /app/resources/views/home.blade.php ENDPATH**/ ?>