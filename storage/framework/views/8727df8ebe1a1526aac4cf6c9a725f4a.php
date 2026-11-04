<?php $__env->startSection('title', __('operator.title_user_detail') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('operator.users.index')).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('operator.users.index')).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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

    <div class="max-w-full">
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
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 px-6 py-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold text-white">
                        <?php echo e(substr($user->name, 0, 1)); ?>

                    </div>
                    <div class="text-white">
                        <h1 class="text-xl font-bold"><?php echo e($user->name); ?></h1>
                        <p class="text-emerald-200 text-sm mt-0.5"><?php echo e($user->email); ?></p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div class="flex flex-wrap gap-3">
                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $user->role,'dot' => true,'label' => ucfirst($user->role)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->role),'dot' => true,'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($user->role))]); ?>
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
                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $user->status,'dot' => true,'label' => ucfirst($user->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->status),'dot' => true,'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($user->status))]); ?>
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

                <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4"><?php echo e(__('operator.account_info')); ?></h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.th_name')); ?></dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($user->name); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.th_email')); ?></dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($user->email); ?></dd>
                        </div>
                        <?php if($user->nip): ?>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.th_nip')); ?></dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($user->nip); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($user->nisn): ?>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.th_nisn')); ?></dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($user->nisn); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.th_role')); ?></dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e(ucfirst($user->role)); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('common.status')); ?></dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e(ucfirst($user->status)); ?></dd>
                        </div>
                        <?php if($user->verified_at): ?>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.verified_at')); ?></dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($user->verified_at->format('d M Y H:i')); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.joined_at')); ?></dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($user->created_at->format('d M Y H:i')); ?></dd>
                        </div>
                    </dl>
                </div>

                <?php
                    $profil = $user->profilPendidikan;
                    $aplikasi = $user->aplikasiPengajar;
                ?>

                <?php if($user->role === 'pelajar'): ?>
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4"><?php echo e(__('operator.profil_pendidikan')); ?></h3>
                        <?php if($profil): ?>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_jalur')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($profil->jalur?->nama ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_jenjang')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($profil->jenjang?->nama ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_tingkat')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($profil->tingkat?->nama ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_institusi')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($profil->institusi ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_program')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($profil->program ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_minat')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($profil->minat ?? '—'); ?></dd>
                                </div>
                            </dl>
                        <?php else: ?>
                            <p class="text-sm text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.profil_pendidikan_kosong')); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($user->role === 'pengajar'): ?>
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4"><?php echo e(__('operator.profil_pengajar')); ?></h3>
                        <?php if($aplikasi): ?>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_pendidikan_tertinggi')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->pendidikan_tertinggi ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_kategori_mapel')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->kategori_mapel ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_jenjang_dan_tingkat')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100"><?php echo e($aplikasi->jenjang_dan_tingkat ?? '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?php echo e(__('operator.profil_pengalaman')); ?></dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100 whitespace-pre-line"><?php echo e($aplikasi->pengalaman_mengajar ?? '—'); ?></dd>
                                </div>
                            </dl>

                            <div class="mt-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2"><?php echo e(__('operator.profil_mapel')); ?></p>
                                <?php if($aplikasi->mata_pelajaran): ?>
                                    <div class="flex flex-wrap gap-1.5">
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
                                <?php else: ?>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.profil_mapel_kosong')); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2"><?php echo e(__('operator.profil_berkas')); ?></p>
                                <div class="space-y-3">
                                    <?php if($aplikasi->cv_path): ?>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                            <button type="button" onclick="toggleDoc('cv-doc')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span id="cv-doc-toggle">Lihat CV</span>
                                            </button>
                                            <a href="<?php echo e(route('operator.users.berkas', ['user' => $user, 'jenis' => 'cv'])); ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Unduh CV
                                            </a>
                                            </div>
                                                <div id="cv-doc" class="hidden mt-3">
                                                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-900 shadow-sm w-full">
                                                        <iframe src="<?php echo e(route('operator.users.berkas', ['user' => $user, 'jenis' => 'cv'])); ?>" class="w-full h-[calc(100vh-400px)] min-h-[400px]" frameborder="0"></iframe>
                                                    </div>
                                                </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($aplikasi->ijazah_path): ?>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                                <button type="button" onclick="toggleDoc('ijazah-doc')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span id="ijazah-doc-toggle">Lihat Ijazah</span>
                                                </button>
                                                <a href="<?php echo e(route('operator.users.berkas', ['user' => $user, 'jenis' => 'ijazah'])); ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Unduh Ijazah
                                                </a>
                                            </div>
                                            <div id="ijazah-doc" class="hidden mt-3">
                                                <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-900 shadow-sm w-full">
                                                    <iframe src="<?php echo e(route('operator.users.berkas', ['user' => $user, 'jenis' => 'ijazah'])); ?>" class="w-full h-[calc(100vh-400px)] min-h-[400px]" frameborder="0"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(! $aplikasi->cv_path && ! $aplikasi->ijazah_path): ?>
                                        <span class="text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.profil_tanpa_berkas')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-slate-500 dark:text-slate-400 italic"><?php echo e(__('operator.profil_pengajar_kosong')); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($user->status === 'pending' || $user->status !== 'pending'): ?>
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4"><?php echo e(__('common.actions')); ?></h3>
                        <div class="flex flex-wrap gap-3">
                            <?php if($user->status === 'pending'): ?>
                                <form action="<?php echo e(route('operator.users.verify', $user)); ?>" method="POST">
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
<?php $component->withAttributes(['type' => 'submit','size' => 'sm']); ?><?php echo e(__('operator.verify_account')); ?> <?php echo $__env->renderComponent(); ?>
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
                                <form action="<?php echo e(route('operator.users.reject', $user)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'submit','color' => 'red','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'red','size' => 'sm']); ?><?php echo e(__('operator.title_reject')); ?> <?php echo $__env->renderComponent(); ?>
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
                            <?php endif; ?>
                            <?php if($user->id !== auth()->id()): ?>
                                <form action="<?php echo e(route('operator.users.destroy', $user)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun <?php echo e(addslashes($user->name)); ?>? Tindakan ini tidak dapat dibatalkan.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <?php if (isset($component)) { $__componentOriginal1edae8760811c7b935175a7435923573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1edae8760811c7b935175a7435923573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.btn-secondary','data' => ['type' => 'submit','color' => 'red','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('btn-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'red','size' => 'sm']); ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus Akun
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
                                </form>
                            <?php endif; ?>
                        </div>
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
    </div>

    <script>
        function toggleDoc(id) {
            var el = document.getElementById(id);
            var toggle = document.getElementById(id + '-toggle');
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                if (toggle) toggle.textContent = toggle.textContent.replace('Lihat', 'Sembunyikan');
            } else {
                el.classList.add('hidden');
                if (toggle) toggle.textContent = toggle.textContent.replace('Sembunyikan', 'Lihat');
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\admin\users\show.blade.php ENDPATH**/ ?>