<?php $themeLabel = __('common.toggle_theme'); ?>


<button
    type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}"
    title="<?php echo e($themeLabel); ?>"
    aria-label="<?php echo e($themeLabel); ?>"
    <?php echo e($attributes->merge(['class' => 'p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 rounded-lg transition-colors focus:outline-none'])); ?>

>
    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
</button>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\theme-toggle.blade.php ENDPATH**/ ?>