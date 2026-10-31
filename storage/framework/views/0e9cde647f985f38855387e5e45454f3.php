<?php $__env->startSection('title', $kelas->nama_kelas . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pengajar.kelas.index')).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.kelas.index')).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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

    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4']); ?>
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100"><?php echo e($kelas->nama_kelas); ?></h1>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?php echo e(__('guru_kelas.kode_label', ['kode' => $kelas->kode_kelas])); ?> &middot; <?php echo e($kelas->siswa->count()); ?> <?php echo e(__('guru_kelas.kata.siswa')); ?>

        <?php if($kelas->kategori): ?> &middot; <?php echo e(__('guru_kelas.kategori_label', ['kategori' => $kelas->kategori])); ?> <?php endif; ?></p>
        <?php if($kelas->deskripsi): ?>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3"><?php echo e($kelas->deskripsi); ?></p>
        <?php endif; ?>
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

    
    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('guru_kelas.kelas.pertemuan')); ?></h2>
            <div class="flex items-center gap-2">
                
                <button
                    x-data
                    x-on:click.prevent="$dispatch('open-modal', 'generate-pertemuan')"
                    class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg font-medium bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 hover:bg-purple-100 transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <?php echo e(__('guru_kelas.generate_ai')); ?>

                </button>
                <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['href' => ''.e(route('pengajar.kelas.pertemuan.create', $kelas)).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.kelas.pertemuan.create', $kelas)).'','size' => 'sm']); ?>
                    + <?php echo e(__('common.create')); ?>

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
            </div>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $kelas->pertemuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pertemuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <a
                    href="<?php echo e(route('pengajar.pertemuan.show', $pertemuan)); ?>"
                    class="flex items-center gap-3 group"
                >
                    <span class="text-sm font-medium text-slate-900 dark:text-slate-100 group-hover:text-emerald-700"><?php echo e(ucfirst(__('guru_kelas.kata.pertemuan'))); ?> <?php echo e($pertemuan->urutan); ?>:</span>
                    <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-emerald-700"><?php echo e($pertemuan->nama_pertemuan); ?></span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        (<?php echo e($pertemuan->materi_count); ?> <?php echo e(__('guru_kelas.kata.materi')); ?><?php echo e($pertemuan->tugas_count > 0 ? ', ' . $pertemuan->tugas_count . ' ' . __('guru_kelas.kata.tugas') : ''); ?><?php echo e($pertemuan->kuis_count > 0 ? ', ' . $pertemuan->kuis_count . ' ' . __('guru_kelas.kata.kuis') : ''); ?>)
                    </span>
                    <?php if($pertemuan->dibuka): ?>
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300"><?php echo e(__('guru_kelas.terbuka')); ?></span>
                    <?php else: ?>
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.terkunci')); ?></span>
                    <?php endif; ?>
                </a>
                <div class="flex items-center gap-2">
                    <form
                        action="<?php echo e($pertemuan->dibuka ? route('pengajar.pertemuan.kunci', $pertemuan) : route('pengajar.pertemuan.buka', $pertemuan)); ?>"
                        method="POST"
                        class="inline"
                    >
                        <?php echo csrf_field(); ?>
                        <button class="text-xs font-medium <?php echo e($pertemuan->dibuka ? 'text-amber-700 dark:text-amber-400 hover:text-amber-800' : 'text-emerald-700 dark:text-emerald-400 hover:text-emerald-800'); ?>">
                            <?php echo e($pertemuan->dibuka ? __('guru_kelas.kunci') : __('guru_kelas.buka')); ?>

                        </button>
                    </form>
                    <a href="<?php echo e(route('pengajar.pertemuan.edit', $pertemuan)); ?>" class="text-xs text-slate-600 dark:text-slate-400 hover:text-slate-800"><?php echo e(__('guru_kelas.edit')); ?></a>
                    <form
                        action="<?php echo e(route('pengajar.pertemuan.destroy', $pertemuan)); ?>"
                        method="POST"
                        class="inline"
                        x-data
                        x-on:submit.prevent="confirm('<?php echo e(__('guru_kelas.kelas.konfirmasi_hapus')); ?>') && $el.submit()"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="text-xs text-red-600 dark:text-red-400 hover:text-red-800"><?php echo e(__('common.delete')); ?></button>
                    </form>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.kelas.belum_ada_pertemuan')); ?></p>
        <?php endif; ?>
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

<div
    x-data="generatePertemuanModal()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    x-on:open-modal.window="if ($event.detail === 'generate-pertemuan') open = true"
    x-on:keydown.escape.window="open = false"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40" x-on:click="open = false"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="relative bg-white dark:bg-slate-900 rounded-lg shadow-xl w-full max-w-lg p-5 z-10">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4"><?php echo e(__('guru_kelas.kelas.generate_pertemuan_ai')); ?></h3>

            <form
                action="<?php echo e(route('pengajar.kelas.generate-pertemuan', $kelas)); ?>"
                method="POST"
                x-on:submit="generateLoading = true"
            >
                <?php echo csrf_field(); ?>

                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?php echo e(__('guru_kelas.kelas.materi_ai_label')); ?></label>
                    <div class="flex gap-2 mb-1">
                        <textarea
                            name="materi_topics"
                            rows="3"
                            x-model="topics"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-700 text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="<?php echo e(__('guru_kelas.kelas.materi_ai_placeholder')); ?>"
                            required
                        ></textarea>
                    </div>
                    <button
                        type="button"
                        x-on:click="saranAi"
                        :disabled="saranLoading"
                        class="text-xs text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 flex items-center gap-1"
                    >
                        <svg x-show="!saranLoading" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <svg x-show="saranLoading" class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span x-show="!saranLoading"><?php echo e(__('guru_kelas.kelas.saran_ai')); ?></span>
                        <span x-show="saranLoading" x-cloak><?php echo e(__('guru_kelas.kelas.meminta_saran')); ?></span>
                    </button>
                </div>

                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?php echo e(__('guru_kelas.jumlah_pertemuan')); ?></label>
                    <input
                        type="number"
                        name="jumlah_pertemuan"
                        x-model="jumlah"
                        min="4"
                        max="20"
                        class="w-24 rounded-lg border border-slate-300 dark:border-slate-700 text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                </div>

                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?php echo e(__('guru_kelas.kelas.catatan_tambahan')); ?> <span class="text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.opsional_kurung')); ?></span></label>
                    <textarea
                        name="catatan"
                        rows="2"
                        x-model="catatan"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="<?php echo e(__('guru_kelas.kelas.catatan_tambahan_placeholder')); ?>"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        x-on:click="open = false"
                        class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800"
                    ><?php echo e(__('common.cancel')); ?></button>
                    <button
                        type="submit"
                        :disabled="generateLoading"
                        class="text-sm px-4 py-2 rounded-lg font-medium transition"
                        :class="generateLoading ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-purple-600 text-white hover:bg-purple-700'"
                    >
                        <span x-show="!generateLoading"><?php echo e(__('guru_kelas.generate')); ?></span>
                        <span x-show="generateLoading" class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <?php echo e(__('guru_kelas.memproses')); ?>

                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function generatePertemuanModal() {
        return {
            open: false,
            topics: '',
            jumlah: 8,

            catatan: '',
            saranLoading: false,
            generateLoading: false,
            async saranAi() {
                this.saranLoading = true;
                try {
                    const res = await fetch('<?php echo e(route('pengajar.kelas.saran-materi', $kelas)); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.topics = json.data;
                    } else if (json.status === 'pending') {
                        alert(<?php echo \Illuminate\Support\Js::from(__('guru_kelas.kelas.alert_saran_pending'))->toHtml() ?>);
                    } else {
                        alert(<?php echo \Illuminate\Support\Js::from(__('guru_kelas.kelas.alert_gagal_prefix'))->toHtml() ?> + json.message);
                    }
                } catch (e) {
                    alert(<?php echo \Illuminate\Support\Js::from(__('guru_kelas.kelas.alert_kesalahan_saran'))->toHtml() ?>);
                } finally {
                    this.saranLoading = false;
                }
            }
        };
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /app/resources/views/guru/kelas/show.blade.php ENDPATH**/ ?>