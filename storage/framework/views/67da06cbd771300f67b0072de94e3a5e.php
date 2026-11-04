<?php $__env->startSection('title', 'Tinjau Pengajuan Mengajar - Operator'); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-6">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => __('operator.pengajuan_mengajar.title'),'subtitle' => __('operator.pengajuan_mengajar.subtitle', ['kelas' => $kelas->nama_kelas])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('operator.pengajuan_mengajar.title')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('operator.pengajuan_mengajar.subtitle', ['kelas' => $kelas->nama_kelas]))]); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['href' => ''.e(route('operator.kelas.index')).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('operator.kelas.index')).'','size' => 'sm']); ?>
                    <?php echo e(__('common.back')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1edae8760811c7b935175a7435923573)): ?>
<?php $attributes = $__attributesOriginal1edae8760811c7b935175a7435923573; ?>
<?php unset($__attributesOriginal1edae8760811c7b935175a7435923573); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1edae8760811c7b935175a7435923573)): ?>
<?php $component = $__componentOriginal1edae8760811c7b935175a7435923573; ?>
<?php unset($__componentOriginal1edae8760811c7b935175a7435923573); ?>
<?php endif; ?>
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

        
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block"><?php echo e(__('navigation.classes')); ?></span>
                    <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($kelas->nama_kelas); ?></span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block"><?php echo e(__('operator.th_kode')); ?></span>
                    <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg text-slate-700 dark:text-slate-300"><?php echo e($kelas->kode_kelas); ?></span>
                </div>
                <?php if($kelas->kategori): ?>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block"><?php echo e(__('operator.th_kategori')); ?></span>
                        <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => 'info','label' => $kelas->kategori]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'info','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kelas->kategori)]); ?>
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
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block"><?php echo e(__('operator.th_teacher')); ?></span>
                    <?php if($kelas->guru): ?>
                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($kelas->guru->name); ?></span>
                    <?php else: ?>
                        <span class="text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.not_assigned')); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($kelas->guru): ?>
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('operator.pengajuan_mengajar.kelas_sudah_diampu_info')); ?></p>
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

        <?php if($pengajuanList->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['message' => __('operator.pengajuan_mengajar.kosong')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('operator.pengajuan_mengajar.kosong'))]); ?>
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
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__currentLoopData = $pengajuanList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pengajuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($pengajuan->pengajar->name); ?></span>
                                        <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING ? 'pending' : ($pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_DISETUJUI ? 'verified' : 'rejected'),'label' => __('operator.pengajuan_mengajar.badge_' . $pengajuan->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING ? 'pending' : ($pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_DISETUJUI ? 'verified' : 'rejected')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('operator.pengajuan_mengajar.badge_' . $pengajuan->status))]); ?>
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
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        <?php echo e($pengajuan->pengajar->email); ?>

                                        &middot; <?php echo e(__('operator.pengajuan_mengajar.diajukan_pada', ['waktu' => $pengajuan->created_at->format('d/m/Y H:i')])); ?>

                                    </p>
                                </div>

                                <?php if($pengajuan->status === \App\Models\PengajuanPengajarKelas::STATUS_PENDING && $kelas->guru_id === null): ?>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="<?php echo e(route('operator.pengajuan-mengajar.setujui', $pengajuan)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php if (isset($component)) { $__componentOriginal490cf816fa75abeeb835565b23a9eae8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal490cf816fa75abeeb835565b23a9eae8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-primary','data' => ['type' => 'submit','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm']); ?><?php echo e(__('operator.approve')); ?> <?php echo $__env->renderComponent(); ?>
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
                                        <form
                                            method="POST"
                                            action="<?php echo e(route('operator.pengajuan-mengajar.tolak', $pengajuan)); ?>"
                                            class="flex items-center gap-2"
                                            x-data
                                            x-on:submit.prevent="confirm('<?php echo e(__('operator.pengajuan_mengajar.konfirmasi_tolak')); ?>') && $el.submit()"
                                        >
                                            <?php echo csrf_field(); ?>
                                            <input
                                                type="text"
                                                name="catatan"
                                                maxlength="1000"
                                                placeholder="<?php echo e(__('operator.pengajuan_mengajar.catatan_placeholder')); ?>"
                                                class="w-52 px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none"
                                            >
                                            <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'submit','size' => 'sm','color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm','color' => 'red']); ?><?php echo e(__('operator.title_reject')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1edae8760811c7b935175a7435923573)): ?>
<?php $attributes = $__attributesOriginal1edae8760811c7b935175a7435923573; ?>
<?php unset($__attributesOriginal1edae8760811c7b935175a7435923573); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1edae8760811c7b935175a7435923573)): ?>
<?php $component = $__componentOriginal1edae8760811c7b935175a7435923573; ?>
<?php unset($__componentOriginal1edae8760811c7b935175a7435923573); ?>
<?php endif; ?>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-3">
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1"><?php echo e(__('operator.pengajuan_mengajar.rencana_label')); ?></p>
                                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line"><?php echo e($pengajuan->rencana_ajar); ?></p>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <?php if($pengajuan->berkas_path): ?>
                                    <a
                                        href="<?php echo e(route('operator.pengajuan-mengajar.berkas', $pengajuan)); ?>"
                                        class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium hover:underline"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <?php echo e(__('operator.pengajuan_mengajar.unduh_berkas')); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.pengajuan_mengajar.tanpa_berkas')); ?></span>
                                <?php endif; ?>
                                <?php if($pengajuan->reviewed_at): ?>
                                    <span class="text-slate-500 dark:text-slate-400">
                                        <?php echo e(__('operator.pengajuan_mengajar.diproses_pada', ['waktu' => $pengajuan->reviewed_at->format('d/m/Y H:i')])); ?>

                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if($pengajuan->catatan_operator): ?>
                                <p class="mt-2 text-xs text-amber-700 dark:text-amber-400">
                                    <?php echo e(__('operator.pengajuan_mengajar.catatan_diberikan', ['catatan' => $pengajuan->catatan_operator])); ?>

                                </p>
                            <?php endif; ?>

                            
                            <?php $aplikasi = $pengajuan->pengajar->aplikasiPengajar; ?>
                            <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 p-3">
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2"><?php echo e(__('operator.pengajuan_mengajar.profil_title')); ?></p>
                                <?php if($aplikasi): ?>
                                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm">
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5"><?php echo e(__('operator.pengajuan_mengajar.profil_pendidikan')); ?></dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->pendidikan_tertinggi ?? '—'); ?></dd>
                                        </div>
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5"><?php echo e(__('operator.pengajuan_mengajar.profil_pengalaman')); ?></dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->pengalaman_mengajar ?? '—'); ?></dd>
                                        </div>
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5"><?php echo e(__('operator.pengajuan_mengajar.profil_kategori_mapel')); ?></dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->kategori_mapel ?? '—'); ?></dd>
                                        </div>
                                        <div class="flex gap-2">
                                            <dt class="text-xs text-slate-500 dark:text-slate-400 shrink-0 pt-0.5"><?php echo e(__('operator.pengajuan_mengajar.profil_jenjang')); ?></dt>
                                            <dd class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->jenjang_dan_tingkat ?? '—'); ?></dd>
                                        </div>
                                    </dl>

                                    <?php if($aplikasi->mata_pelajaran): ?>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('operator.pengajuan_mengajar.profil_mapel_klaim')); ?></span>
                                            <?php $__currentLoopData = $aplikasi->mata_pelajaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mapel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => 'default','label' => $mapel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'default','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mapel)]); ?>
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
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                        <?php if($aplikasi->cv_path): ?>
                                            <a href="<?php echo e(route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'cv'])); ?>" class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                <?php echo e(__('operator.pengajuan_mengajar.unduh_cv')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if($aplikasi->ijazah_path): ?>
                                            <a href="<?php echo e(route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'ijazah'])); ?>" class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                <?php echo e(__('operator.pengajuan_mengajar.unduh_ijazah')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if(!$aplikasi->cv_path && !$aplikasi->ijazah_path): ?>
                                            <span class="text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.pengajuan_mengajar.profil_tanpa_berkas')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.pengajuan_mengajar.profil_kosong')); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\operator\pengajuan-mengajar\show.blade.php ENDPATH**/ ?>