<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'file',
    'id' => 'file',
    'label' => null,
    'currentPath' => null,
    'currentName' => null,
    'currentType' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name' => 'file',
    'id' => 'file',
    'label' => null,
    'currentPath' => null,
    'currentName' => null,
    'currentType' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $currentUrl = $currentPath ? Storage::url($currentPath) : null;
    $currentKind = match (strtolower($currentType ?? '')) {
        'pdf' => 'pdf',
        'png', 'jpg', 'jpeg', 'gif', 'webp' => 'image',
        default => 'other',
    };
?>

<div
    class="mb-5"
    x-data="{
        nama: null,
        url: null,
        jenis: null,
        pilih(e) {
            const f = e.target.files && e.target.files[0];
            if (this.url && this.url.startsWith('blob:')) URL.revokeObjectURL(this.url);
            if (!f) { this.nama = null; this.url = null; this.jenis = null; return; }
            this.nama = f.name;
            this.jenis = f.type.startsWith('image/') ? 'image' : (f.type === 'application/pdf' ? 'pdf' : 'other');
            this.url = this.jenis === 'other' ? null : URL.createObjectURL(f);
        }
    }"
>
    <label for="<?php echo e($id); ?>" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2"><?php echo e($label ?? __('guru_kelas.materi.file')); ?></label>
    <input
        type="file"
        name="<?php echo e($name); ?>"
        id="<?php echo e($id); ?>"
        class="w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 dark:file:bg-emerald-600/20 file:text-emerald-700 dark:file:text-emerald-300 hover:file:bg-emerald-100"
        x-on:change="pilih($event)"
    >

    <?php if($currentUrl): ?>
        <div class="mt-3">
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2"><?php echo e(__('guru_kelas.materi.file_saat_ini', ['file' => $currentName ?? basename($currentPath)])); ?></p>
            <?php if($currentKind === 'pdf'): ?>
                <div class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 70vh;">
                    <iframe src="<?php echo e($currentUrl); ?>" class="w-full h-full border-0"></iframe>
                </div>
            <?php elseif($currentKind === 'image'): ?>
                <div class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                    <img src="<?php echo e($currentUrl); ?>" alt="<?php echo e($currentName); ?>" class="max-w-full mx-auto rounded-lg">
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.pratinjau_tidak_tersedia')); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <template x-if="nama">
        <div class="mt-3">
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2"><?php echo e(__('guru_kelas.materi.pratinjau_baru')); ?>: <span class="font-medium text-slate-700 dark:text-slate-300" x-text="nama"></span></p>
            <template x-if="jenis === 'pdf'">
                <div class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 70vh;">
                    <iframe :src="url" class="w-full h-full border-0"></iframe>
                </div>
            </template>
            <template x-if="jenis === 'image'">
                <div class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                    <img :src="url" alt="" class="max-w-full mx-auto rounded-lg">
                </div>
            </template>
            <template x-if="jenis === 'other'">
                <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('guru_kelas.materi.pratinjau_tidak_tersedia')); ?></p>
            </template>
        </div>
    </template>
</div>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\components\file-preview.blade.php ENDPATH**/ ?>