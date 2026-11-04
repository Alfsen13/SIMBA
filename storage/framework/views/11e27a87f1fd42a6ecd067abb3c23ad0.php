<?php $__env->startSection('title', __('guru_kelas.materi.edit_title') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e($materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id)).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id)).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('guru_kelas.materi.edit_title')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.materi.edit_title'))]); ?>
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
        <form
            action="<?php echo e(route('pengajar.materi.update', $materi)); ?>"
            method="POST"
            enctype="multipart/form-data"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'judul','label' => __('guru_kelas.materi.judul'),'value' => old('judul', $materi->judul),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'judul','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.materi.judul')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('judul', $materi->judul)),'required' => true]); ?>
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
                <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'bab','label' => __('guru_kelas.materi.bab'),'value' => old('bab', $materi->bab)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bab','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.materi.bab')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('bab', $materi->bab))]); ?>
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
            <div
                x-data="{
                    kelasId: '<?php echo e($materi->kelas_id); ?>',
                    pertemuanList: <?php echo e($pertemuanList->map(fn($p, $k) => $p->map(fn($v) => ['id' => $v->id, 'nama' => $v->nama]))->toJson()); ?>

                }"
            >
                <?php if (isset($component)) { $__componentOriginal67ad07a4b593e690d435fee92e6413bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal67ad07a4b593e690d435fee92e6413bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-select','data' => ['name' => 'kelas_id','label' => __('guru_kelas.materi.kelas'),'options' => $kelasList->pluck('nama_kelas', 'id')->toArray(),'value' => $materi->kelas_id,'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'kelas_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.materi.kelas')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kelasList->pluck('nama_kelas', 'id')->toArray()),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($materi->kelas_id),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal67ad07a4b593e690d435fee92e6413bb)): ?>
<?php $attributes = $__attributesOriginal67ad07a4b593e690d435fee92e6413bb; ?>
<?php unset($__attributesOriginal67ad07a4b593e690d435fee92e6413bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal67ad07a4b593e690d435fee92e6413bb)): ?>
<?php $component = $__componentOriginal67ad07a4b593e690d435fee92e6413bb; ?>
<?php unset($__componentOriginal67ad07a4b593e690d435fee92e6413bb); ?>
<?php endif; ?>
                <div class="mt-5">
                    <label for="pertemuan_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2"><?php echo e(__('guru_kelas.materi.pertemuan_opsional')); ?></label>
                    <select
                        name="pertemuan_id"
                        id="pertemuan_id"
                        class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"
                    >
                        <option value=""><?php echo e(__('guru_kelas.materi.pilih_pertemuan')); ?></option>
                        <template x-for="p in (pertemuanList[kelasId] || [])" :key="p.id">
                            <option
                                x-bind:value="p.id"
                                x-text="p.nama"
                                x-bind:selected="p.id == <?php echo e($materi->pertemuan_id ?? 'null'); ?>"
                            ></option>
                        </template>
                    </select>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginale428ff3664af329988e8d13451374b90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale428ff3664af329988e8d13451374b90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.file-preview','data' => ['label' => __('guru_kelas.materi.file'),'currentPath' => $materi->file_path,'currentType' => $materi->file_type]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('file-preview'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('guru_kelas.materi.file')),'current-path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($materi->file_path),'current-type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($materi->file_type)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale428ff3664af329988e8d13451374b90)): ?>
<?php $attributes = $__attributesOriginale428ff3664af329988e8d13451374b90; ?>
<?php unset($__attributesOriginale428ff3664af329988e8d13451374b90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale428ff3664af329988e8d13451374b90)): ?>
<?php $component = $__componentOriginale428ff3664af329988e8d13451374b90; ?>
<?php unset($__componentOriginale428ff3664af329988e8d13451374b90); ?>
<?php endif; ?>
            <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.kosongkan_file')); ?></p>

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
                <?php echo e(__('guru_kelas.update')); ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\materi\edit.blade.php ENDPATH**/ ?>