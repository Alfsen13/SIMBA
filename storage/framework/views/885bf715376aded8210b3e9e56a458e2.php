<?php $__env->startSection('title', __('guru_analitik.prediksi.monitor_page_title') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $badgeVariant = [
            'aman' => 'success',
            'perlu_pantau' => 'warning',
            'berisiko_tinggi' => 'danger',
        ];

        $suffix = [
            'rata_durasi_belajar' => ' mnt',
            'persen_tugas_terkumpul' => '%',
            'tren_nilai' => '',
            'persen_kehadiran' => '%',
            'rata_nilai' => '',
            'jumlah_pertemuan_tidak_aktif' => '',
        ];

        $indicatorCards = $fitur
            ? collect(\App\Services\FiturPertemuanService::fiturModel())
                ->map(fn ($key) => [
                    'label' => \App\Services\FiturPertemuanService::FEATURE_LABELS[$key],
                    'value' => $fitur->{$key},
                    'suffix' => $suffix[$key] ?? '',
                ])->values()->all()
            : [];
    ?>

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('guru_analitik.prediksi.monitor_page_title'),'subtitle' => __('guru_analitik.prediksi.monitor_subtitle')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_analitik.prediksi.monitor_page_title')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_analitik.prediksi.monitor_subtitle'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('pengajar.prediksi')); ?>" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                &larr; <?php echo e(__('guru_analitik.prediksi.back_to_list')); ?>

            </a>
            
            <div x-data="{ generating: false }">
                <form
                    action="<?php echo e(route('pengajar.prediksi.run')); ?>"
                    method="POST"
                    @submit="generating = true"
                >
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','size' => 'sm','xBind:disabled' => 'generating']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm','x-bind:disabled' => 'generating']); ?>
                        <svg x-show="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg x-show="generating" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-show="!generating"><?php echo e(__('guru_analitik.prediksi.run')); ?></span>
                        <span x-show="generating" x-cloak><?php echo e(__('guru_analitik.prediksi.calculating')); ?></span>
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

                <?php if (isset($component)) { $__componentOriginal115e82920da0ed7c897ee494af74b9d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal115e82920da0ed7c897ee494af74b9d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.loading-overlay','data' => ['judul' => __('guru_analitik.prediksi.overlay_risk'),'heading' => __('guru_analitik.prediksi.overlay_heading'),'catatan' => __('guru_analitik.prediksi.overlay_note')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('loading-overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['judul' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_analitik.prediksi.overlay_risk')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_analitik.prediksi.overlay_heading')),'catatan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_analitik.prediksi.overlay_note'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal115e82920da0ed7c897ee494af74b9d8)): ?>
<?php $attributes = $__attributesOriginal115e82920da0ed7c897ee494af74b9d8; ?>
<?php unset($__attributesOriginal115e82920da0ed7c897ee494af74b9d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal115e82920da0ed7c897ee494af74b9d8)): ?>
<?php $component = $__componentOriginal115e82920da0ed7c897ee494af74b9d8; ?>
<?php unset($__componentOriginal115e82920da0ed7c897ee494af74b9d8); ?>
<?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>
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

    <?php if($siswaOptions->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.no_student_in_class')); ?></p>
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
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <label for="siswa-select" class="text-sm font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                    <?php echo e(__('guru_analitik.prediksi.select_student')); ?>

                </label>
                <select id="siswa-select" onchange="window.location.href = this.value"
                    class="w-full sm:max-w-md rounded-lg border-slate-200 dark:border-slate-800 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <?php $__currentLoopData = $siswaOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opsi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e(route('pengajar.prediksi.show', ['siswa' => $opsi->id])); ?>" <?php if($siswa && $siswa->id === $opsi->id): echo 'selected'; endif; ?>>
                            <?php echo e($opsi->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
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

        <?php if($siswa): ?>
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-600/20 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold">
                            <?php echo e(mb_strtoupper(mb_substr($siswa->name, 0, 1))); ?>

                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e($siswa->name); ?></h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                <?php echo e($siswa->kelasDiikuti->pluck('nama_kelas')->join(', ') ?: '-'); ?>

                            </p>
                        </div>
                    </div>
                    <?php if($pred): ?>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <div class="flex items-center gap-2" title="<?php echo e(__('guru_analitik.prediksi.model_output_title')); ?>">
                                <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.model_prediction')); ?></span>
                                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($badgeVariant[$pred->hasil] ?? 'default').'','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($badgeVariant[$pred->hasil] ?? 'default').'','dot' => true]); ?>
                                    <?php echo e($pred->hasil); ?>

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
                            </div>
                            <div class="flex items-center gap-2" title="<?php echo e(__('guru_analitik.prediksi.threshold_status_title')); ?>">
                                <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.threshold_short')); ?></span>
                                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($badgeVariant[$pred->labelAmbang()] ?? 'default').'','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($badgeVariant[$pred->labelAmbang()] ?? 'default').'','dot' => true]); ?>
                                    <?php echo e($pred->labelAmbang()); ?>

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
                                <span class="text-sm font-semibold <?php echo e($pred->skor_risiko >= 40 ? 'text-red-600 dark:text-red-400' : ($pred->skor_risiko >= 20 ? 'text-amber-700 dark:text-amber-400' : 'text-green-800 dark:text-green-400')); ?>">
                                    <?php echo e(number_format($pred->skor_risiko, 1)); ?>

                                </span>
                            </div>
                            <?php if($rf?->kategori_risiko_rf): ?>
                                
                                <div class="flex items-center gap-2" title="<?php echo e(__('guru_analitik.prediksi.rf_badge_title')); ?>">
                                    <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.rf_label')); ?></span>
                                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($badgeVariant[$rf->kategori_risiko_rf] ?? 'default').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($badgeVariant[$rf->kategori_risiko_rf] ?? 'default').'']); ?>
                                        <?php echo e($rf->kategori_risiko_rf); ?>

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
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
                <?php $__empty_1 = true; $__currentLoopData = $indicatorCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => '!p-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-4']); ?>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1"><?php echo e($card['label']); ?></p>
                        <p class="text-xl font-bold text-slate-900 dark:text-slate-100 <?php echo e(isset($card['value']) && in_array($card['label'], ['Pertemuan Tidak Aktif', 'Tren Nilai'], true) && $card['value'] < 0 ? 'text-red-600' : ''); ?>">
                            <?php echo e(is_null($card['value']) ? '-' : $card['value'] . ($card['suffix'] ?? '')); ?>

                        </p>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => '!p-4 col-span-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-4 col-span-full']); ?>
                        <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.indicator_unavailable')); ?></p>
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
                <?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('guru_analitik.prediksi.indicator_progress')); ?></h3>
                    <div id="indicator-buttons" class="flex flex-wrap gap-1.5"></div>
                </div>
                <?php if($history->isEmpty()): ?>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <?php echo e(__('guru_analitik.prediksi.history_unavailable')); ?>

                    </p>
                <?php else: ?>
                    <div class="relative h-72">
                        <canvas id="chart-indikator"></canvas>
                    </div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                    <?php echo e(__('guru_analitik.prediksi.next_forecast_title')); ?>

                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                    <?php echo e(__('guru_analitik.prediksi.next_forecast_desc1')); ?>

                    <?php echo e(__('guru_analitik.prediksi.next_forecast_desc2')); ?>

                </p>

                <?php if(! $mendatang['siap']): ?>
                    <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800 p-4">
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            <?php echo e($mendatang['alasan'] ?? __('guru_analitik.prediksi.next_forecast_unavailable')); ?>

                        </p>
                    </div>
                <?php else: ?>
                    <?php
                        $sekarang = $mendatang['sekarang_nilai'];
                        $prediksi = $mendatang['nilai'];
                        $delta = ($sekarang === null || $prediksi === null) ? null : round($prediksi - $sekarang, 1);
                        $baik = $delta === null ? null : $delta >= 0;
                        $warna = $baik === null ? 'text-slate-500' : ($baik ? 'text-green-800 dark:text-green-400' : 'text-red-600');
                    ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-4">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.next_meeting_grade')); ?></p>
                            <div class="flex items-baseline gap-2 mt-1">
                                <span class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    <?php echo e(number_format($prediksi, 1)); ?>

                                </span>
                                <?php if($delta !== null): ?>
                                    <span class="text-sm font-semibold <?php echo e($warna); ?>">
                                        <?php echo e($delta > 0 ? '+' : ''); ?><?php echo e(number_format($delta, 1)); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <?php echo e(__('guru_analitik.prediksi.last_meeting')); ?>

                                <?php if($sekarang === null): ?>
                                    <span class="italic"><?php echo e(__('guru_analitik.prediksi.no_data')); ?></span>
                                <?php else: ?>
                                    <?php echo e(number_format($sekarang, 1)); ?>

                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if(($mendatang['persentase_meleset'] ?? null) !== null): ?>
                            <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.meleset_title')); ?></p>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-xl font-bold text-slate-900 dark:text-slate-100"><?php echo e(number_format($mendatang['persentase_meleset'], 1)); ?>%</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <?php echo e(__('guru_analitik.prediksi.meleset_note', ['ideal' => number_format((float) ($mendatang['nilai_ideal'] ?? 100), 0)])); ?>

                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                        // Metadata ramalan yang tersimpan, ditampilkan supaya nilai
                        // prediksi tidak berdiri sendiri tanpa identitas pertemuan
                        // sumber/target dan status kelengkapan datanya.
                        $metaRamalan = [
                            __('guru_analitik.prediksi.forecast_source') => $mendatang['pertemuan'] ?? null,
                            __('guru_analitik.prediksi.forecast_target') => $mendatang['pertemuan_target'] ?? null,
                            __('guru_analitik.prediksi.status_data') => ($mendatang['status_data'] ?? null) === 'lengkap'
                                ? __('guru_analitik.prediksi.status_lengkap')
                                : __('guru_analitik.prediksi.status_tidak_lengkap'),
                            __('guru_analitik.prediksi.nilai_aktual') => ($mendatang['nilai_aktual'] ?? null) === null
                                ? null
                                : number_format($mendatang['nilai_aktual'], 1),
                            __('guru_analitik.prediksi.mae_individual') => ($mendatang['mae_individual'] ?? null) === null
                                ? null
                                : number_format($mendatang['mae_individual'], 2),
                            __('guru_analitik.prediksi.model_version') => $mendatang['model_versi'] ?? null,
                        ];
                        $bm = $mendatang['benchmark'] ?? null;
                        $labelPertemuan = ! empty($mendatang['pertemuan']) ? ' — '.$mendatang['pertemuan'] : '';
                    ?>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-1">
                        <?php $__currentLoopData = $metaRamalan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labelMeta => $nilaiMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 dark:border-slate-800 py-1">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400"><?php echo e($labelMeta); ?></span>
                                <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 text-right">
                                    <?php echo e($nilaiMeta ?? __('guru_analitik.prediksi.no_value_yet')); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <?php if(! empty($mendatang['input'])): ?>
                        <div class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4">
                            <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                <?php echo e(__('guru_analitik.prediksi.forecast_basis', ['suffix' => $labelPertemuan])); ?>

                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                                <?php $setengahInput = (int) ceil(count($mendatang['input']) / 2); ?>
                                <?php $__currentLoopData = [array_slice($mendatang['input'], 0, $setengahInput), array_slice($mendatang['input'], $setengahInput)]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kolom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <table class="w-full text-xs">
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <?php $__currentLoopData = $kolom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $in): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td class="py-1.5 pr-3 text-slate-600 dark:text-slate-400"><?php echo e($in['label']); ?></td>
                                                    <td class="py-1.5 text-right font-medium text-slate-800 dark:text-slate-200">
                                                        <?php if($in['nilai'] === null): ?>
                                                            <span class="italic text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.no_data')); ?></span>
                                                        <?php else: ?>
                                                            <?php echo e(number_format($in['nilai'], 1)); ?><?php echo e($in['suffix']); ?>

                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                <?php echo e(__('guru_analitik.prediksi.forecast_meaning_1')); ?>

                                <span class="font-medium text-slate-600 dark:text-slate-400"><?php echo e(number_format($prediksi, 1)); ?></span>.
                                <?php echo e(__('guru_analitik.prediksi.forecast_meaning_2')); ?>

                            </p>
                        </div>
                    <?php endif; ?>

                    <details class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <?php echo e(__('guru_analitik.prediksi.why_forecast', ['model' => $bm['pemenang_nama'] ?? __('guru_analitik.prediksi.svr_linear')])); ?>

                        </summary>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 mb-2">
                            <?php echo e(__('guru_analitik.prediksi.why_intro')); ?> <em><?php echo e(__('guru_analitik.prediksi.why_all')); ?></em>
                            <?php echo e(__('guru_analitik.prediksi.why_mapping')); ?> <span class="font-medium"><?php echo e(__('guru_analitik.prediksi.why_target')); ?></span>.
                            <?php echo e(__('guru_analitik.prediksi.why_pairs')); ?>

                            &ldquo;<?php echo e(__('guru_analitik.prediksi.why_pair_from')); ?> &rarr; <?php echo e(__('guru_analitik.prediksi.why_pair_to')); ?>&rdquo;.
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                            <span class="font-medium text-slate-600 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.how_title')); ?></span>
                            <?php echo e(__('guru_analitik.prediksi.how_line1')); ?> <?php echo e(__('guru_analitik.prediksi.how_line2a')); ?> (&epsilon;) <?php echo e(__('guru_analitik.prediksi.how_line2b')); ?>

                            <?php echo e(__('guru_analitik.prediksi.how_line3')); ?>

                        </p>

                        <?php if($bm && ! empty($bm['benchmark'])): ?>

                            <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 p-3 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                                <p class="font-semibold text-slate-600 dark:text-slate-400 mb-1"><?php echo e(__('guru_analitik.prediksi.glossary_title')); ?></p>
                                <ul class="space-y-1">
                                    <li>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">MAE</span> (<?php echo e(__('guru_analitik.prediksi.mae_name')); ?>) =
                                        <?php echo e(__('guru_analitik.prediksi.mae_def')); ?> (0&ndash;100).
                                        <?php echo e(__('guru_analitik.prediksi.mae_example')); ?>

                                        <span class="font-medium"><?php echo e(__('guru_analitik.prediksi.smaller_is_better')); ?></span>
                                    </li>
                                    <li>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">RMSE</span> (<?php echo e(__('guru_analitik.prediksi.rmse_name')); ?>) =
                                        <?php echo e(__('guru_analitik.prediksi.rmse_def')); ?> &ge; MAE.
                                        <span class="font-medium"><?php echo e(__('guru_analitik.prediksi.smaller_is_better')); ?></span>
                                    </li>
                                    <li>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">R&sup2;</span> (<?php echo e(__('guru_analitik.prediksi.r2_name')); ?>) =
                                        <?php echo e(__('guru_analitik.prediksi.r2_def')); ?> &le; 1.
                                        <?php echo e(__('guru_analitik.prediksi.r2_def2')); ?>

                                        <span class="font-medium"><?php echo e(__('guru_analitik.prediksi.r2_negative')); ?></span>
                                        <?php echo e(__('guru_analitik.prediksi.r2_def3')); ?> <span class="font-medium">n/a</span>
                                        <?php echo e(__('guru_analitik.prediksi.r2_def4')); ?>

                                    </li>
                                </ul>
                                <p class="mt-2">
                                    <?php echo e(__('guru_analitik.prediksi.tested_at', ['waktu' => $bm['dilatih_pada'] ?? __('guru_analitik.prediksi.unknown_time')])); ?>

                                </p>
                            </div>
                        <?php endif; ?>
                    </details>

                    
                    <details class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <?php echo e(__('guru_analitik.prediksi.rf_details_title')); ?>

                        </summary>

                        <?php if($benchmarkRf && ($benchmarkRf['akurasi'] ?? null) !== null): ?>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                <?php echo e(__('guru_analitik.prediksi.rf_details_intro')); ?>

                            </p>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 mt-2 text-xs">
                                <?php $__currentLoopData = [
                                    __('guru_analitik.prediksi.rf_accuracy') => $benchmarkRf['akurasi'] ?? null,
                                    __('guru_analitik.prediksi.rf_precision') => $benchmarkRf['precision_macro'] ?? null,
                                    __('guru_analitik.prediksi.rf_recall') => $benchmarkRf['recall_macro'] ?? null,
                                    __('guru_analitik.prediksi.rf_f1') => $benchmarkRf['f1_macro'] ?? null,
                                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labelRf => $nilaiRf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 dark:border-slate-800 py-1">
                                        <dt class="text-slate-500 dark:text-slate-400"><?php echo e($labelRf); ?></dt>
                                        <dd class="font-medium text-slate-800 dark:text-slate-200"><?php echo e(number_format((float) $nilaiRf * 100, 1)); ?>%</dd>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 dark:border-slate-800 py-1">
                                    <dt class="text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.rf_test_samples')); ?></dt>
                                    <dd class="font-medium text-slate-800 dark:text-slate-200"><?php echo e($benchmarkRf['test_samples'] ?? '-'); ?></dd>
                                </div>
                            </dl>

                            <?php if(! empty($benchmarkRf['confusion_matrix'])): ?>
                                <p class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 mt-3 mb-1"><?php echo e(__('guru_analitik.prediksi.rf_matrix_title')); ?></p>
                                <table class="text-[11px]">
                                    <thead>
                                        <tr>
                                            <th class="pr-3 text-left font-medium text-slate-500 dark:text-slate-400"></th>
                                            <?php $__currentLoopData = \App\Services\KlasifikasiRisikoService::LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labelKolom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <th class="px-2 py-0.5 font-medium text-slate-500 dark:text-slate-400"><?php echo e($labelKolom); ?></th>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $benchmarkRf['confusion_matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labelBaris => $kolom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="pr-3 py-0.5 font-medium text-slate-600 dark:text-slate-400"><?php echo e($labelBaris); ?></td>
                                                <?php $__currentLoopData = $kolom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <td class="px-2 py-0.5 text-center text-slate-700 dark:text-slate-300"><?php echo e($jumlah); ?></td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-3"><?php echo e(__('guru_analitik.prediksi.rf_caveat')); ?></p>
                        <?php else: ?>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2"><?php echo e(__('guru_analitik.prediksi.rf_unavailable')); ?></p>
                        <?php endif; ?>
                    </details>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4"><?php echo e(__('guru_analitik.prediksi.engagement_heading')); ?></h3>
                <div class="relative h-60">
                    <canvas id="chart-aktivitas"></canvas>
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

            
            <script id="prediksi-show-data" type="application/json"><?php echo json_encode($chartData, 15, 512) ?></script>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.prediksi.select_student_hint')); ?></p>
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
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\prediksi\show.blade.php ENDPATH**/ ?>