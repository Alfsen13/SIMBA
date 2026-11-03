<?php $__env->startSection('content'); ?>
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Pengaturan AI Pribadi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Gunakan API AI milik Anda sendiri (OpenAI, Nvidia NIM, Ollama, Gemini, Anthropic, atau endpoint custom)
                untuk generate soal, remedial, dan AI Tutor. Jika tidak dikonfigurasi, sistem menggunakan provider global.
            </p>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded text-emerald-800 dark:text-emerald-300 text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('pengajar.ai-settings.update')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (isset($component)) { $__componentOriginal67ad07a4b593e690d435fee92e6413bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal67ad07a4b593e690d435fee92e6413bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-select','data' => ['name' => 'provider','label' => 'Provider AI','value' => $setting->provider,'options' => [
                            'ollama'      => 'Ollama (Local)',
                            'gemini'      => 'Google Gemini',
                            'nvidia_nim'  => 'Nvidia NIM',
                            'openai'      => 'OpenAI',
                            'anthropic'   => 'Anthropic (Claude)',
                            'custom'      => 'Endpoint Custom',
                        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'provider','label' => 'Provider AI','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setting->provider),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                            'ollama'      => 'Ollama (Local)',
                            'gemini'      => 'Google Gemini',
                            'nvidia_nim'  => 'Nvidia NIM',
                            'openai'      => 'OpenAI',
                            'anthropic'   => 'Anthropic (Claude)',
                            'custom'      => 'Endpoint Custom',
                        ])]); ?>
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

                    <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'model','label' => 'Model','value' => old('model', $setting->model),'placeholder' => 'Contoh: qwen2.5:7b, gemini-3.8-flash, meta/llama-3.1-70b-instruct']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'model','label' => 'Model','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('model', $setting->model)),'placeholder' => 'Contoh: qwen2.5:7b, gemini-3.8-flash, meta/llama-3.1-70b-instruct']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'endpoint','label' => 'Endpoint URL','value' => old('endpoint', $setting->endpoint),'placeholder' => 'https://api.openai.com/v1','hint' => 'Diabaikan untuk Gemini/OpenAI/NVIDIA NIM (endpoint bawaan). Diperlukan untuk Ollama dan Custom.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'endpoint','label' => 'Endpoint URL','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('endpoint', $setting->endpoint)),'placeholder' => 'https://api.openai.com/v1','hint' => 'Diabaikan untuk Gemini/OpenAI/NVIDIA NIM (endpoint bawaan). Diperlukan untuk Ollama dan Custom.']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'api_key','label' => 'API Key','type' => 'password','placeholder' => 'Kosongkan jika tidak ingin mengubah','hint' => 'Disimpan terenkripsi. Kosongkan jika tidak ingin mengubah yang lama.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'api_key','label' => 'API Key','type' => 'password','placeholder' => 'Kosongkan jika tidak ingin mengubah','hint' => 'Disimpan terenkripsi. Kosongkan jika tidak ingin mengubah yang lama.']); ?>
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

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Aktifkan</label>
                        <input
                            type="checkbox"
                            name="enabled"
                            <?php echo e(old('enabled', $setting->enabled ?? true) ? 'checked' : ''); ?>

                            class="rounded-slate bg-slate-50 dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 focus:ring-2"
                        />
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php if (isset($component)) { $__componentOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93a7e4fbb8709cb7edbcf616ab99cd14 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'options[temperature]','label' => 'Temperature','type' => 'number','step' => '0.1','min' => '0','max' => '2','value' => old('options.temperature', $setting->options['temperature'] ?? 0.7),'placeholder' => '0.7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'options[temperature]','label' => 'Temperature','type' => 'number','step' => '0.1','min' => '0','max' => '2','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('options.temperature', $setting->options['temperature'] ?? 0.7)),'placeholder' => '0.7']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-input','data' => ['name' => 'options[max_tokens]','label' => 'Max Tokens','type' => 'number','value' => old('options.max_tokens', $setting->options['max_tokens'] ?? 4096),'placeholder' => '4096']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'options[max_tokens]','label' => 'Max Tokens','type' => 'number','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('options.max_tokens', $setting->options['max_tokens'] ?? 4096)),'placeholder' => '4096']); ?>
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

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="btn-primary flex-1">
                        Simpan Pengaturan
                    </button>

                    <button
                        type="button"
                        id="test-ai"
                        class="btn-secondary flex-1"
                    >
                        Test Koneksi
                    </button>
                </div>

                <div id="test-result" class="mt-3 hidden p-3 rounded text-sm"></div>
            </div>
        </form>
    </div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('test-ai').addEventListener('click', async () => {
    const btn = document.getElementById('test-ai');
    const result = document.getElementById('test-result');

    btn.disabled = true;
    btn.textContent = 'Menguji...';
    result.classList.add('hidden');
    result.className = 'mt-3 p-3 rounded text-sm';

    try {
        const res = await fetch('<?php echo e(route('pengajar.ai-settings.test')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            },
        });

        const data = await res.json();

        if (data.ok) {
            result.className = 'mt-3 p-3 rounded text-sm bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300';
            result.textContent = '✓ Berhasil terhubung: ' + data.reply;
        } else {
            result.className = 'mt-3 p-3 rounded text-sm bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300';
            result.textContent = '✗ Gagal: ' + (data.error ?? 'Tidak diketahui');
        }
    } catch (e) {
        result.className = 'mt-3 p-3 rounded text-sm bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300';
        result.textContent = '✗ Terjadi kesalahan jaringan.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Test Koneksi';
        result.classList.remove('hidden');
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /app/resources/views/guru/ai-settings/edit.blade.php ENDPATH**/ ?>