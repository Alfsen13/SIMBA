<?php $__env->startSection('title', __('guru_soal.title_buat_kuis') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pengajar.pertemuan.show', $pertemuan)).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.pertemuan.show', $pertemuan)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('guru_soal.daftar_kuis_baru'),'subtitle' => ''.e(__('guru_soal.kelas')).': '.e($pertemuan->kelas->nama_kelas).' &middot; '.e(__('guru_soal.pertemuan')).': '.e($pertemuan->nama_pertemuan).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.daftar_kuis_baru')),'subtitle' => ''.e(__('guru_soal.kelas')).': '.e($pertemuan->kelas->nama_kelas).' &middot; '.e(__('guru_soal.pertemuan')).': '.e($pertemuan->nama_pertemuan).'']); ?>
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

    <form
        action="<?php echo e(route('pengajar.kuis.store')); ?>"
        method="POST"
        class="w-full"
        x-data="kuisBuilder([], { pertemuanId: <?php echo e($pertemuan->id); ?>, generateUrl: '<?php echo e(route('pengajar.kuis.generate')); ?>', bloomLevels: <?php echo e(Js::from($bloomLevels)); ?>, sumberKonteks: <?php echo e(Js::from($sumberKonteks)); ?> })"
    >
        <?php echo csrf_field(); ?>
        <input type="hidden" name="kelas_id" value="<?php echo e($pertemuan->kelas_id); ?>">
        <input type="hidden" name="pertemuan_id" value="<?php echo e($pertemuan->id); ?>">

        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mb-4 space-y-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 space-y-5']); ?>
            <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'judul','label' => __('guru_soal.judul_kuis'),'value' => ''.e(old('judul')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'judul','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.judul_kuis')),'value' => ''.e(old('judul')).'','required' => true]); ?>
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
            <?php if (isset($component)) { $__componentOriginale6e8742f0e11a5c16e0b807772c0bffc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale6e8742f0e11a5c16e0b807772c0bffc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-columns','data' => ['cols' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-columns'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => 2]); ?>
                <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'durasi_menit','type' => 'number','label' => __('guru_soal.durasi_menit_label'),'value' => ''.e(old('durasi_menit', 30)).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'durasi_menit','type' => 'number','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.durasi_menit_label')),'value' => ''.e(old('durasi_menit', 30)).'','required' => true]); ?>
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
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale6e8742f0e11a5c16e0b807772c0bffc)): ?>
<?php $attributes = $__attributesOriginale6e8742f0e11a5c16e0b807772c0bffc; ?>
<?php unset($__attributesOriginale6e8742f0e11a5c16e0b807772c0bffc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale6e8742f0e11a5c16e0b807772c0bffc)): ?>
<?php $component = $__componentOriginale6e8742f0e11a5c16e0b807772c0bffc; ?>
<?php unset($__componentOriginale6e8742f0e11a5c16e0b807772c0bffc); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal8e14382acda27b00a310067bbfac5044 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e14382acda27b00a310067bbfac5044 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.materi-utama-select','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('materi-utama-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e14382acda27b00a310067bbfac5044)): ?>
<?php $attributes = $__attributesOriginal8e14382acda27b00a310067bbfac5044; ?>
<?php unset($__attributesOriginal8e14382acda27b00a310067bbfac5044); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e14382acda27b00a310067bbfac5044)): ?>
<?php $component = $__componentOriginal8e14382acda27b00a310067bbfac5044; ?>
<?php unset($__componentOriginal8e14382acda27b00a310067bbfac5044); ?>
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
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3"><?php echo e(__('guru_soal.generate_dengan_ai')); ?></h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                <?php echo e(__('guru_soal.ai_draf_kuis')); ?>

            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                <div>
                    <label for="gen_jumlah" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1"><?php echo e(__('guru_soal.jumlah_soal')); ?></label>
                    <input
                        id="gen_jumlah"
                        type="number"
                        min="1"
                        max="12"
                        x-model.number="genJumlah"
                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </div>
                <div>
                    <label for="gen_kesulitan" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1"><?php echo e(__('guru_soal.tingkat_kesulitan')); ?></label>
                    <select
                        id="gen_kesulitan"
                        x-model="genKesulitan"
                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                        <option value="mudah"><?php echo e(__('guru_soal.kesulitan_mudah')); ?></option>
                        <option value="sedang"><?php echo e(__('guru_soal.kesulitan_sedang')); ?></option>
                        <option value="sulit"><?php echo e(__('guru_soal.kesulitan_sulit')); ?></option>
                    </select>
                </div>
                <div>
                    <label for="gen_topik" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1"><?php echo e(__('guru_soal.topik_opsional')); ?></label>
                    <input
                        id="gen_topik"
                        type="text"
                        x-model="genTopik"
                        placeholder="<?php echo e(__('guru_soal.topik_placeholder')); ?>"
                        class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </div>
            </div>
            <?php if (isset($component)) { $__componentOriginal773f43a625b8a3f24ef34fe143dd0d39 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal773f43a625b8a3f24ef34fe143dd0d39 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.konteks-soal-picker','data' => ['sumberKonteks' => $sumberKonteks]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('konteks-soal-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['sumber-konteks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sumberKonteks)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal773f43a625b8a3f24ef34fe143dd0d39)): ?>
<?php $attributes = $__attributesOriginal773f43a625b8a3f24ef34fe143dd0d39; ?>
<?php unset($__attributesOriginal773f43a625b8a3f24ef34fe143dd0d39); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal773f43a625b8a3f24ef34fe143dd0d39)): ?>
<?php $component = $__componentOriginal773f43a625b8a3f24ef34fe143dd0d39; ?>
<?php unset($__componentOriginal773f43a625b8a3f24ef34fe143dd0d39); ?>
<?php endif; ?>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="generateSoal"
                    :disabled="generating"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg x-show="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg x-show="generating" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!generating"><?php echo e(__('guru_soal.generate')); ?></span>
                    <span x-show="generating" x-cloak><?php echo e(__('guru_soal.menyusun')); ?></span>
                </button>
                <span x-show="generating" x-cloak class="text-sm text-emerald-700 dark:text-emerald-400">
                    <?php echo e(__('guru_soal.ai_menyusun')); ?>

                </span>
            </div>
            <div class="mt-3">
                <p x-show="genMessage" x-cloak class="text-sm text-green-700 dark:text-green-400" x-text="genMessage"></p>
                <p x-show="genError" x-cloak class="text-sm text-red-600 dark:text-red-400" x-text="genError"></p>
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
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('guru_soal.soal')); ?></h2>
                <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'button','size' => 'sm','@click' => 'addQuestion']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','size' => 'sm','@click' => 'addQuestion']); ?>
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    <?php echo e(__('guru_soal.tambah_soal_manual')); ?>

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

            <template x-for="(q, i) in questions" :key="i">
                <div class="border border-slate-200 dark:border-slate-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300"><?php echo e(__('guru_soal.soal_prefix')); ?> <span x-text="i + 1"></span></span>
                        <button
                            type="button"
                            @click="removeQuestion(i)"
                            class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm flex items-center gap-1"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            <?php echo e(__('common.delete')); ?>

                        </button>
                    </div>

                    <?php if (isset($component)) { $__componentOriginalc013e934671bcc0e86e6a95eed24d346 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc013e934671bcc0e86e6a95eed24d346 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.akurasi-soal','data' => ['soal' => 'q']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('akurasi-soal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['soal' => 'q']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc013e934671bcc0e86e6a95eed24d346)): ?>
<?php $attributes = $__attributesOriginalc013e934671bcc0e86e6a95eed24d346; ?>
<?php unset($__attributesOriginalc013e934671bcc0e86e6a95eed24d346); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc013e934671bcc0e86e6a95eed24d346)): ?>
<?php $component = $__componentOriginalc013e934671bcc0e86e6a95eed24d346; ?>
<?php unset($__componentOriginalc013e934671bcc0e86e6a95eed24d346); ?>
<?php endif; ?>

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            <?php echo e(__('guru_soal.pertanyaan')); ?>

                        </label>
                        <input
                            type="text"
                            x-model="q.pertanyaan"
                            placeholder="<?php echo e(__('guru_soal.ph_masukkan_pertanyaan')); ?>"
                            class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                            <?php echo e(__('guru_soal.label_bloom')); ?>

                        </label>
                        <select
                            x-model="q.bloom"
                            class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value=""><?php echo e(__('guru_soal.bloom_tidak_ditentukan')); ?></option>
                            <template x-for="l in bloomLevels" :key="l.code">
                                <option :value="l.code" x-text="l.code + ' — ' + l.name_id"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <template x-for="(pil, j) in q.pilihan" :key="j">
                            <div class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    :name="'jawaban_' + i"
                                    :value="j"
                                    x-model="q.jawaban"
                                    class="shrink-0 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500"
                                >
                                <input
                                    type="text"
                                    x-model="q.pilihan[j]"
                                    :placeholder="'Pilihan ' + String.fromCharCode(65 + j)"
                                    class="block w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                                
                                <span
                                    x-show="Number(q.jawaban) === Number(j)"
                                    x-cloak
                                    class="shrink-0 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                                ><?php echo e(__('guru_soal.kunci')); ?></span>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <?php echo e(__('guru_soal.pilih_radio')); ?>

                    </p>
                </div>
            </template>

            <template x-if="questions.length === 0">
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['message' => __('guru_soal.kosong_soal_kuis')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.kosong_soal_kuis'))]); ?>
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
            </template>

            <input
                type="hidden"
                name="soal"
                x-bind:value="JSON.stringify(questions)"
            >
            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','class' => 'w-full mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'w-full mt-4']); ?>
                <?php echo e(__('guru_soal.simpan_kuis')); ?>

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
        <?php if (isset($component)) { $__componentOriginal115e82920da0ed7c897ee494af74b9d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal115e82920da0ed7c897ee494af74b9d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.loading-overlay','data' => ['judul' => __('guru_soal.loading_kuis')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('loading-overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['judul' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_soal.loading_kuis'))]); ?>
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
    </form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\kuis\create.blade.php ENDPATH**/ ?>