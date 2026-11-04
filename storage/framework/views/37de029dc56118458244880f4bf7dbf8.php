<?php
    $sebalik = $sebalik ?? false;
?>
<?php if($nilai === null): ?>
    <span class="text-slate-500 dark:text-slate-400">-</span>
<?php else: ?>
    <?php
        $v = (float) $nilai;
        // $sebalik = true untuk metrik yang makin kecil makin baik
        // (persen terlambat / persen terlewat).
        $warn = $sebalik ? $v >= 50 : $v < 60;
        $bad = $sebalik ? $v >= 75 : $v < 40;
    ?>
    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'rounded px-2 py-0.5 text-xs font-semibold',
        'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' => $bad,
        'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' => $warn && ! $bad,
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-300' => ! $warn,
    ]); ?>"><?php echo e(number_format($v, 1)); ?>%</span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\fitur-siswa\_nilai.blade.php ENDPATH**/ ?>