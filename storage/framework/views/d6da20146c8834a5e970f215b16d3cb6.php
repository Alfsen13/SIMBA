<?php $__env->startSection('title', __('guru_analitik.remedial.detail_title') . ' - SIMBA'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-4xl mx-auto">
        <?php if (isset($component)) { $__componentOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5426bd0bea02df2e6dd2a60e50fa4c01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-link','data' => ['href' => ''.e(route('pengajar.remedial.index')).'','label' => __('common.back')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('pengajar.remedial.index')).'','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('common.back'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="p-4 sm:p-6">
                <div class="flex items-center gap-2 mb-1">
                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => ''.e($remedial->status === 'belum_dibaca' ? 'warning' : 'success').'','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($remedial->status === 'belum_dibaca' ? 'warning' : 'success').'','dot' => true]); ?>
                        <?php echo e($remedial->status === 'belum_dibaca' ? __('guru_analitik.remedial.status_unread') : ($remedial->status === 'selesai' ? __('guru_analitik.remedial.status_done') : __('guru_analitik.remedial.status_read'))); ?>

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
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        <?php echo e($remedial->generated_at?->format('d M Y H:i') ?: '-'); ?>

                    </span>
                </div>

                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-3 mb-4">
                    <?php echo e($remedial->topik_lemah ?: __('guru_analitik.remedial.fallback_title')); ?>

                </h1>

                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 mb-4">
                    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                        <?php echo e(__('guru_analitik.remedial.student_info')); ?>

                    </h2>
                    <p class="text-slate-900 dark:text-slate-100 font-medium">
                        <?php echo e($remedial->siswa?->name ?: '-'); ?>

                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <?php echo e($remedial->siswa?->email ?: '-'); ?>

                    </p>
                </div>

                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                        <?php echo e(__('guru_analitik.remedial.summary')); ?>

                    </h2>
                    <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                        <?php echo e($remedial->ringkasan); ?>

                    </div>
                </div>

                <?php if(empty($remedial->tugas_remedial) && $remedial->latihan): ?>
                    <div class="border-t pt-6 mb-4">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                            <?php echo e(__('guru_analitik.remedial.exercise')); ?>

                        </h2>
                        <?php if(! empty($remedial->latihan_items)): ?>
                            <?php if (isset($component)) { $__componentOriginal35907f94b383debd63c0ff7dc8eb12b7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal35907f94b383debd63c0ff7dc8eb12b7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.remedial-latihan','data' => ['items' => $remedial->latihan_items]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('remedial-latihan'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($remedial->latihan_items)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal35907f94b383debd63c0ff7dc8eb12b7)): ?>
<?php $attributes = $__attributesOriginal35907f94b383debd63c0ff7dc8eb12b7; ?>
<?php unset($__attributesOriginal35907f94b383debd63c0ff7dc8eb12b7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal35907f94b383debd63c0ff7dc8eb12b7)): ?>
<?php $component = $__componentOriginal35907f94b383debd63c0ff7dc8eb12b7; ?>
<?php unset($__componentOriginal35907f94b383debd63c0ff7dc8eb12b7); ?>
<?php endif; ?>
                        <?php else: ?>
                            <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                                <?php echo e($remedial->latihan); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if(! empty($remedial->bloom_tujuan)): ?>
                    <div class="border-t pt-6 mb-4">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                            <?php echo e(__('guru_analitik.remedial.learning_objectives')); ?>

                            <span class="normal-case font-normal"><?php echo e(__('guru_analitik.remedial.bloom_taxonomy')); ?></span>
                        </h2>
                        <ul class="space-y-2">
                            <?php $__currentLoopData = $remedial->bloom_tujuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $kode = strtoupper((string) ($t['kode'] ?? '')); ?>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center shrink-0 rounded-md text-xs font-bold px-2 py-1
                                        <?php echo e(match($kode) {
                                            'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                            'C2' => 'bg-blue-50 text-blue-700',
                                            'C3' => 'bg-emerald-50 text-emerald-700',
                                            'C4' => 'bg-purple-50 text-purple-700',
                                            'C5' => 'bg-amber-50 text-amber-700',
                                            'C6' => 'bg-green-50 text-green-700',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                        }); ?>">
                                        <?php echo e($kode); ?>

                                    </span>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        <span class="font-medium block text-slate-800 dark:text-slate-200"><?php echo e($t['level'] ?? '-'); ?></span>
                                        <?php echo e($t['tujuan'] ?? ''); ?>

                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if(! empty($remedial->tugas_remedial)): ?>
                    <div class="border-t pt-6 mb-4">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                            <?php echo e(__('guru_analitik.remedial.ai_task_title')); ?>

                        </h2>
                        <?php if($remedial->hasil_remedial): ?>
                            <div class="mb-4 p-4 rounded-lg border
                                <?php echo e($remedial->bloom_tercapai ? 'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-800' : 'bg-amber-50 border-amber-200'); ?>">
                                <p class="font-semibold <?php echo e($remedial->bloom_tercapai ? 'text-green-700 dark:text-green-300' : 'text-amber-700'); ?>">
                                    <?php echo e($remedial->bloom_tercapai
                                        ? __('guru_analitik.remedial.mastered')
                                        : __('guru_analitik.remedial.not_mastered')); ?>

                                </p>
                                <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 whitespace-pre-wrap"><?php echo e($remedial->mastery_alasan); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                    <?php echo e(__('guru_analitik.remedial.meta_ai', [
                                        'tanggal' => $remedial->dinilai_at?->format('d M Y H:i') ?: '-',
                                        'konteks' => __('guru_analitik.remedial.bloom_context'),
                                    ])); ?>

                                </p>
                            </div>
                        <?php elseif($remedial->jawaban_remedial): ?>
                            <div class="p-4 rounded-lg border border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-500/10 text-sm text-blue-700 dark:text-blue-300 mb-4">
                                <?php echo e(__('guru_analitik.remedial.awaiting_review')); ?>

                            </div>
                        <?php else: ?>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4"><?php echo e(__('guru_analitik.remedial.not_started')); ?></p>
                        <?php endif; ?>

                        <?php $__currentLoopData = $remedial->tugas_remedial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $kode = strtoupper((string) ($s['bloom'] ?? ''));
                                $hasil = is_array($remedial->hasil_remedial ?? null)
                                    ? collect($remedial->hasil_remedial)->firstWhere('indeks', $i)
                                    : null;
                            ?>
                            <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($i + 1); ?>. <?php if (isset($component)) { $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.math','data' => ['value' => $s['pertanyaan'] ?? __('guru_analitik.remedial.question_fallback')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('math'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s['pertanyaan'] ?? __('guru_analitik.remedial.question_fallback'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $attributes = $__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__attributesOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9)): ?>
<?php $component = $__componentOriginalfaf561ff154c0cfe1516915dcf9843c9; ?>
<?php unset($__componentOriginalfaf561ff154c0cfe1516915dcf9843c9); ?>
<?php endif; ?></p>
                                    <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded
                                        <?php echo e(match($kode) {
                                            'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                            'C2' => 'bg-blue-50 text-blue-600',
                                            'C3' => 'bg-emerald-50 text-emerald-700',
                                            'C4' => 'bg-purple-50 text-purple-600',
                                            'C5' => 'bg-amber-50 text-amber-600',
                                            'C6' => 'bg-green-50 text-green-700',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                        }); ?>">
                                        <?php echo e($kode); ?> — <?php echo e($s['bloom'] ?? ''); ?>

                                    </span>
                                </div>
                                <?php if(! empty($s['referensi'])): ?>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        <span class="font-medium text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.remedial.answer_reference')); ?></span> <?php echo e($s['referensi']); ?>

                                    </p>
                                <?php endif; ?>
                                <?php if($remedial->jawaban_remedial): ?>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 whitespace-pre-wrap">
                                        <span class="text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.remedial.student_answer')); ?></span> <?php echo e($remedial->jawaban_remedial[$i] ?? '-'); ?>

                                    </p>
                                <?php endif; ?>
                                <?php if($hasil): ?>
                                    <?php
                                        $variant = match($hasil['status'] ?? '') {
                                            'benar' => 'verified',
                                            'mendekati' => 'warning',
                                            default => 'danger',
                                        };
                                    ?>
                                    <div class="flex items-center gap-2 mt-2">
                                        <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $variant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variant)]); ?><?php echo e(ucfirst($hasil['status'] ?? 'mendekati')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                                        <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.remedial.score_label')); ?> <?php echo e($hasil['skor'] ?? '-'); ?>/100</span>
                                        <?php if($hasil['bloom'] ?? null): ?>
                                            <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('guru_analitik.remedial.measured_at', ['level' => $hasil['bloom']])); ?></span>
                                        <?php endif; ?>
                                        <?php if(! empty($hasil['alasan'])): ?>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">— <?php echo e($hasil['alasan']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <div class="border-t pt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span><?php echo e(__('guru_analitik.remedial.provider_label', ['provider' => $remedial->provider_llm ?: '-'])); ?></span>
                    <?php if($remedial->prediksiAi): ?>
                        <span><?php echo e(__('guru_analitik.remedial.risk_score_label', ['skor' => $remedial->prediksiAi->skor_risiko ?? '-'])); ?></span>
                    <?php endif; ?>
                </div>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\remedial\show.blade.php ENDPATH**/ ?>