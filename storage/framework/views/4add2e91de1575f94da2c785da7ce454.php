<?php $__env->startSection('title', __('auth.register_button') . ' - SIMBA'); ?>

<?php $__env->startSection('container_width', 'max-w-2xl'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-7">
        <div class="text-center mb-5">
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                <?php echo e(__('auth.register_title')); ?>

            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?php echo e(__('auth.register_hint')); ?>

            </p>
        </div>

        <script type="application/json" id="reg-master"><?php echo json_encode($regMaster, 15, 512) ?></script>
        <script type="application/json" id="mapel-by-kategori"><?php echo json_encode($mapelByKategori, 15, 512) ?></script>

        <form
            action="<?php echo e(route('register')); ?>"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-4"
            x-data="{
                role: '<?php echo e(in_array(old('role'), ['pelajar', 'pengajar'], true) ? old('role') : 'pelajar'); ?>',
                jalurId: '<?php echo e(preg_replace('/[^0-9]/', '', (string) old('jalur_id'))); ?>',
                jenjangId: '<?php echo e(preg_replace('/[^0-9]/', '', (string) old('jenjang_id'))); ?>',
                tingkatId: '<?php echo e(preg_replace('/[^0-9]/', '', (string) old('tingkat_id'))); ?>',
                mapelInput: '',
                mapel: <?php echo json_encode(old('mata_pelajaran', []), 512) ?>,
                ...JSON.parse(document.getElementById('reg-master').textContent),
                get jenjangList() { return this.jenjang[this.jalurId] || [] },
                get tingkatList() { return this.tingkat[this.jenjangId] || [] },
                get jalurKode() {
                    const j = this.jalur.find(x => String(x.id) === String(this.jalurId));
                    return j ? j.kode : null;
                },
                get butuhInstitusi() { return this.jalurId !== '' && this.jalurKode !== 'self_learning' },
                tambahMapel(nama = null) {
                    const t = (nama || this.mapelInput).trim();
                    if (t && !this.mapel.some(m => m.toLowerCase() === t.toLowerCase())) {
                        this.mapel.push(t);
                    }
                    this.mapelInput = '';
                },
                tambahDariPreview(nama) {
                    this.tambahMapel(nama);
                }
            }"
            x-init="window.__simmaTambahMapel = (nama) => this.tambahDariPreview(nama)"
        >
            <?php echo csrf_field(); ?>

            <div class="grid sm:grid-cols-2 gap-x-4">
                <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'name','label' => __('auth.name'),'value' => ''.e(old('name')).'','placeholder' => ''.e(__('auth.name_placeholder')).'','required' => true,'icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.name')),'value' => ''.e(old('name')).'','placeholder' => ''.e(__('auth.name_placeholder')).'','required' => true,'icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'email','type' => 'email','label' => 'Email','value' => ''.e(old('email')).'','placeholder' => 'nama@email.com','required' => true,'icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','type' => 'email','label' => 'Email','value' => ''.e(old('email')).'','placeholder' => 'nama@email.com','required' => true,'icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>']); ?>
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
            </div>

            <div>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">
                    <?php echo e(__('auth.role')); ?>

                </p>
                <div class="grid grid-cols-2 gap-3">
                    <label
                        @click="role = 'pelajar'; if (jalurKode !== 'nonformal') { jalurId = ''; jenjangId = ''; tingkatId = ''; }"
                        :class="role === 'pelajar' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'"
                        class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors focus-within:ring-2 focus-within:ring-emerald-500/40"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="pelajar"
                            x-model="role"
                            class="sr-only"
                        >
                        <div
                            :class="role === 'pelajar' ? 'bg-emerald-700' : 'bg-slate-100 dark:bg-slate-800'"
                            class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                        >
                            <svg
                                :class="role === 'pelajar' ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('auth.role_pelajar')); ?></div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400"><?php echo e(__('auth.role_hint_pelajar')); ?></div>
                        </div>
                    </label>

                    <label
                        @click="role = 'pengajar'"
                        :class="role === 'pengajar' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'"
                        class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors focus-within:ring-2 focus-within:ring-emerald-500/40"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="pengajar"
                            x-model="role"
                            class="sr-only"
                        >
                        <div
                            :class="role === 'pengajar' ? 'bg-slate-800 dark:bg-slate-600' : 'bg-slate-100 dark:bg-slate-800'"
                            class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                        >
                            <svg
                                :class="role === 'pengajar' ? 'text-white' : 'text-slate-500 dark:text-slate-400'"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('auth.role_pengajar')); ?></div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400"><?php echo e(__('auth.role_hint_pengajar')); ?></div>
                        </div>
                    </label>
                </div>

                <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1" role="alert">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo e($message); ?>

                    </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div
                x-show="role === 'pengajar'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'nip','label' => 'NIP ('.e(__('auth.opsional')).')','value' => ''.e(old('nip')).'','placeholder' => ''.e(__('auth.nip_placeholder')).'','icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'nip','label' => 'NIP ('.e(__('auth.opsional')).')','value' => ''.e(old('nip')).'','placeholder' => ''.e(__('auth.nip_placeholder')).'','icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>']); ?>
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
            </div>

            
            <template x-if="role === 'pengajar'">
                <?php if (isset($component)) { $__componentOriginalb95fb6157a10b6449458ef38a3dd045c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb95fb6157a10b6449458ef38a3dd045c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-section','data' => ['title' => __('auth.aplikasi_pengajar'),'hint' => __('auth.aplikasi_pengajar_hint')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.aplikasi_pengajar')),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.aplikasi_pengajar_hint'))]); ?>
                    <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'pendidikan_tertinggi','label' => __('auth.pendidikan_tertinggi_label'),'value' => ''.e(old('pendidikan_tertinggi')).'','placeholder' => ''.e(__('auth.pendidikan_tertinggi_placeholder')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pendidikan_tertinggi','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.pendidikan_tertinggi_label')),'value' => ''.e(old('pendidikan_tertinggi')).'','placeholder' => ''.e(__('auth.pendidikan_tertinggi_placeholder')).'']); ?>
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

                    <?php if (isset($component)) { $__componentOriginalcc0154580828f80bdab5d7fe416ed74a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcc0154580828f80bdab5d7fe416ed74a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-textarea','data' => ['name' => 'pengalaman_mengajar','label' => __('auth.pengalaman_mengajar_label'),'value' => ''.e(old('pengalaman_mengajar')).'','placeholder' => ''.e(__('auth.pengalaman_mengajar_placeholder')).'','rows' => 3]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pengalaman_mengajar','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.pengalaman_mengajar_label')),'value' => ''.e(old('pengalaman_mengajar')).'','placeholder' => ''.e(__('auth.pengalaman_mengajar_placeholder')).'','rows' => 3]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcc0154580828f80bdab5d7fe416ed74a)): ?>
<?php $attributes = $__attributesOriginalcc0154580828f80bdab5d7fe416ed74a; ?>
<?php unset($__attributesOriginalcc0154580828f80bdab5d7fe416ed74a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcc0154580828f80bdab5d7fe416ed74a)): ?>
<?php $component = $__componentOriginalcc0154580828f80bdab5d7fe416ed74a; ?>
<?php unset($__componentOriginalcc0154580828f80bdab5d7fe416ed74a); ?>
<?php endif; ?>

                    <div class="mb-3" x-data="mapelPreview">
                        <label for="kategori_mapel" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.kategori_mapel_label')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="kategori_mapel"
                            id="kategori_mapel"
                            x-model="kategori"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 <?php $__errorArgs = ['kategori_mapel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                        >
                            <option value=""><?php echo e(__('komponen.pilih_opsi')); ?></option>
                            <?php $__currentLoopData = $kategoriMapelList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($kat); ?>" <?php echo e(old('kategori_mapel') === $kat ? 'selected' : ''); ?>><?php echo e($kat); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <?php echo e(__('auth.kategori_mapel_hint')); ?>

                        </p>
                        <div class="mt-2" x-show="kategori && mapelByKategori[kategori]?.length > 0" x-transition.opacity>
                            <p class="text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                <?php echo e(__('auth.kategori_mapel_preview_label')); ?>

                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="mapel in mapelByKategori[kategori]">
                                    <span
                                        class="px-2 py-0.5 text-[11px] rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 cursor-pointer hover:bg-emerald-100 dark:hover:bg-emerald-800 transition"
                                        @click="tambahDariPreview(mapel)"
                                        x-text="mapel"
                                    ></span>
                                </template>
                            </div>
                        </div>
                        <div class="mt-2" x-show="!kategori || !mapelByKategori[kategori]?.length" x-transition.opacity>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400" x-text="__('auth.kategori_mapel_preview_empty')"></p>
                        </div>
                        <?php $__errorArgs = ['kategori_mapel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'jenjang_dan_tingkat','label' => __('auth.jenjang_dan_tingkat_label'),'value' => ''.e(old('jenjang_dan_tingkat')).'','placeholder' => ''.e(__('auth.jenjang_dan_tingkat_placeholder')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'jenjang_dan_tingkat','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.jenjang_dan_tingkat_label')),'value' => ''.e(old('jenjang_dan_tingkat')).'','placeholder' => ''.e(__('auth.jenjang_dan_tingkat_placeholder')).'']); ?>
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

                    
                    <div class="mb-3">
                        <label for="mapel_input" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.mata_pelajaran_label')); ?>

                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(<?php echo e(__('auth.opsional')); ?>)</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                id="mapel_input"
                                type="text"
                                x-model="mapelInput"
                                @keydown.enter.prevent="tambahMapel()"
                                maxlength="255"
                                placeholder="<?php echo e(__('auth.mata_pelajaran_placeholder')); ?>"
                                class="flex-1 min-w-0 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400"
                            >
                            <button
                                type="button"
                                @click="tambahMapel()"
                                title="<?php echo e(__('auth.mata_pelajaran_tambah')); ?>"
                                class="px-3.5 py-2 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shrink-0"
                            >
                                +
                            </button>
                        </div>
                        <template x-for="(m, i) in mapel" :key="i">
                            <input type="hidden" name="mata_pelajaran[]" :value="m">
                        </template>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="(m, i) in mapel" :key="'c' + i">
                                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 text-xs rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <span x-text="m"></span>
                                    <button
                                        type="button"
                                        @click="mapel.splice(i, 1)"
                                        class="w-4 h-4 rounded-full flex items-center justify-center text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800"
                                        aria-label="Hapus"
                                    >
                                        &times;
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="cv" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                <?php echo e(__('auth.cv_label')); ?>

                                <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(<?php echo e(__('auth.file_hint')); ?>)</span>
                            </label>
                            <input
                                type="file"
                                name="cv"
                                id="cv"
                                accept=".pdf,.doc,.docx"
                                class="block w-full text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 cursor-pointer"
                            >
                            <?php $__errorArgs = ['cv'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="ijazah" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                <?php echo e(__('auth.ijazah_label')); ?>

                                <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(<?php echo e(__('auth.file_hint')); ?>)</span>
                            </label>
                            <input
                                type="file"
                                name="ijazah"
                                id="ijazah"
                                accept=".pdf,.doc,.docx"
                                class="block w-full text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 cursor-pointer"
                            >
                            <?php $__errorArgs = ['ijazah'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb95fb6157a10b6449458ef38a3dd045c)): ?>
<?php $attributes = $__attributesOriginalb95fb6157a10b6449458ef38a3dd045c; ?>
<?php unset($__attributesOriginalb95fb6157a10b6449458ef38a3dd045c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb95fb6157a10b6449458ef38a3dd045c)): ?>
<?php $component = $__componentOriginalb95fb6157a10b6449458ef38a3dd045c; ?>
<?php unset($__componentOriginalb95fb6157a10b6449458ef38a3dd045c); ?>
<?php endif; ?>
            </template>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('mapelPreview', () => {
                        const data = JSON.parse(document.getElementById('mapel-by-kategori').textContent);
                        return {
                            kategori: <?php echo json_encode(old('kategori_mapel'), 15, 512) ?>,
                            mapelByKategori: data || {},
                        };
                    });
                });
            </script>

            <div
                x-show="role === 'pelajar'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'nisn','label' => 'NISN','value' => ''.e(old('nisn')).'','placeholder' => ''.e(__('auth.nisn_placeholder')).'','icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'nisn','label' => 'NISN','value' => ''.e(old('nisn')).'','placeholder' => ''.e(__('auth.nisn_placeholder')).'','icon' => '<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>']); ?>
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
            </div>

            
            <template x-if="role === 'pelajar'">
                <?php if (isset($component)) { $__componentOriginalb95fb6157a10b6449458ef38a3dd045c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb95fb6157a10b6449458ef38a3dd045c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-section','data' => ['title' => __('auth.profil_pendidikan'),'hint' => __('auth.profil_pendidikan_hint')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.profil_pendidikan')),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.profil_pendidikan_hint'))]); ?>
                    <div class="mb-3">
                        <label for="jalur_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.jalur_label')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="jalur_id"
                            id="jalur_id"
                            x-model="jalurId"
                            @change="jenjangId = ''; tingkatId = ''"
                            required
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 <?php $__errorArgs = ['jalur_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                            <option value=""><?php echo e(__('komponen.pilih_opsi')); ?></option>
                            <?php $__currentLoopData = $jalurPayload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($j['id']); ?>"><?php echo e($j['nama']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p
                            x-show="jalurKode === 'self_learning'"
                            x-cloak
                            class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            <?php echo e(__('auth.belajar_mandiri_hint')); ?>

                        </p>
                        <?php $__errorArgs = ['jalur_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3" x-show="jenjangList.length > 0" x-transition.opacity>
                        <label for="jenjang_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.jenjang_label')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="jenjang_id"
                            id="jenjang_id"
                            x-model="jenjangId"
                            @change="tingkatId = ''"
                            :required="jalurKode === 'formal' || jalurKode === 'nonformal'"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 <?php $__errorArgs = ['jenjang_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                            <option value=""><?php echo e(__('komponen.pilih_opsi')); ?></option>
                            <template x-for="j in jenjangList" :key="j.id">
                                <option :value="j.id" x-text="j.nama"></option>
                            </template>
                        </select>
                        <?php $__errorArgs = ['jenjang_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3" x-show="tingkatList.length > 0" x-transition.opacity>
                        <label for="tingkat_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.tingkat_label')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="tingkat_id"
                            id="tingkat_id"
                            x-model="tingkatId"
                            :required="jalurKode === 'formal' || jalurKode === 'nonformal'"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150 <?php $__errorArgs = ['tingkat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                            <option value=""><?php echo e(__('komponen.pilih_opsi')); ?></option>
                            <template x-for="t in tingkatList" :key="t.id">
                                <option :value="t.id" x-text="t.nama"></option>
                            </template>
                        </select>
                        <?php $__errorArgs = ['tingkat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3" x-show="butuhInstitusi" x-transition.opacity>
                        <label for="institusi" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.institusi_label')); ?>

                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(<?php echo e(__('auth.opsional')); ?>)</span>
                        </label>
                        <input
                            type="text"
                            name="institusi"
                            id="institusi"
                            value="<?php echo e(old('institusi')); ?>"
                            maxlength="255"
                            placeholder="<?php echo e(__('auth.institusi_placeholder')); ?>"
                            class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 <?php $__errorArgs = ['institusi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                        <?php $__errorArgs = ['institusi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3" x-show="jalurKode === 'nonformal'" x-transition.opacity>
                        <label for="program" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.program_label')); ?>

                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(<?php echo e(__('auth.opsional')); ?>)</span>
                        </label>
                        <input
                            type="text"
                            name="program"
                            id="program"
                            value="<?php echo e(old('program')); ?>"
                            maxlength="255"
                            placeholder="<?php echo e(__('auth.program_placeholder')); ?>"
                            class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 <?php $__errorArgs = ['program'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                        <?php $__errorArgs = ['program'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-1">
                        <label for="minat" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            <?php echo e(__('auth.minat_label')); ?>

                            <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(<?php echo e(__('auth.opsional')); ?>)</span>
                        </label>
                        <input
                            type="text"
                            name="minat"
                            id="minat"
                            value="<?php echo e(old('minat')); ?>"
                            maxlength="500"
                            placeholder="<?php echo e(__('auth.minat_placeholder')); ?>"
                            class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition placeholder:text-slate-500 dark:placeholder:text-slate-400 <?php $__errorArgs = ['minat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                        <?php $__errorArgs = ['minat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb95fb6157a10b6449458ef38a3dd045c)): ?>
<?php $attributes = $__attributesOriginalb95fb6157a10b6449458ef38a3dd045c; ?>
<?php unset($__attributesOriginalb95fb6157a10b6449458ef38a3dd045c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb95fb6157a10b6449458ef38a3dd045c)): ?>
<?php $component = $__componentOriginalb95fb6157a10b6449458ef38a3dd045c; ?>
<?php unset($__componentOriginalb95fb6157a10b6449458ef38a3dd045c); ?>
<?php endif; ?>
            </template>

            <?php if (isset($component)) { $__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-password','data' => ['name' => 'password','label' => __('auth.password_label'),'placeholder' => __('auth.password_placeholder'),'required' => true,'minlength' => 8,'autocomplete' => 'new-password']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-password'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'password','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.password_label')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.password_placeholder')),'required' => true,'minlength' => 8,'autocomplete' => 'new-password']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6)): ?>
<?php $attributes = $__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6; ?>
<?php unset($__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6)): ?>
<?php $component = $__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6; ?>
<?php unset($__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-password','data' => ['name' => 'password_confirmation','label' => __('auth.password_confirm'),'placeholder' => __('auth.password_confirm_placeholder'),'required' => true,'autocomplete' => 'new-password']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-password'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'password_confirmation','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.password_confirm')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('auth.password_confirm_placeholder')),'required' => true,'autocomplete' => 'new-password']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6)): ?>
<?php $attributes = $__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6; ?>
<?php unset($__attributesOriginalc37ca9136bdcf43a2a5af8030190c3d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6)): ?>
<?php $component = $__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6; ?>
<?php unset($__componentOriginalc37ca9136bdcf43a2a5af8030190c3d6); ?>
<?php endif; ?>

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
                <?php echo e(__('auth.register_button')); ?>

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
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

        <div class="border-t border-slate-200 dark:border-slate-800 my-5"></div>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            <?php echo e(__('auth.have_account')); ?>

            <a
                href="<?php echo e(route('login')); ?>"
                class="font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition"
            >
                <?php echo e(__('auth.login_link')); ?>

            </a>
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views/auth/register.blade.php ENDPATH**/ ?>