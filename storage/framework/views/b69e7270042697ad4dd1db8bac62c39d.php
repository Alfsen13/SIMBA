<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo e(__('guru_analitik.nilai.pdf.document_title', ['nama' => $siswa->name])); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #6b7280; }
        .header { border-bottom: 2px solid #374151; padding-bottom: 10px; margin-bottom: 16px; }
        .meta { font-size: 11px; margin-top: 6px; line-height: 1.6; }
        .summary { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .summary td { border: 1px solid #d1d5db; padding: 6px 8px; width: 25%; }
        .summary .label { color: #6b7280; font-size: 10px; }
        .summary .value { font-size: 14px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; }
        table.data th { background: #f3f4f6; text-align: left; font-size: 11px; }
        .center { text-align: center; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e(__('guru_analitik.nilai.pdf.heading')); ?></h1>
        <div class="muted"><?php echo e(__('guru_analitik.nilai.pdf.tagline')); ?></div>
        <div class="meta">
            <strong><?php echo e(__('guru_analitik.nilai.pdf.name_label')); ?></strong> <?php echo e($siswa->name); ?>

            <?php if($siswa->nisn): ?> &nbsp;|&nbsp; <strong><?php echo e(__('guru_analitik.nilai.pdf.nisn_label')); ?></strong> <?php echo e($siswa->nisn); ?> <?php endif; ?>
            &nbsp;|&nbsp; <strong><?php echo e(__('guru_analitik.nilai.pdf.class_label')); ?></strong> <?php echo e($kelasDipilih?->nama_kelas ?? __('guru_analitik.nilai.pdf.all_classes')); ?>

            <br>
            <strong><?php echo e(__('guru_analitik.nilai.pdf.teacher_label')); ?></strong> <?php echo e($guru->name); ?>

            &nbsp;|&nbsp; <strong><?php echo e(__('guru_analitik.nilai.pdf.printed_label')); ?></strong> <?php echo e(now()->translatedFormat('d F Y H:i')); ?>

        </div>
    </div>

    <table class="summary">
        <tr>
            <td><div class="label"><?php echo e(__('guru_analitik.nilai.rata_kuis')); ?></div><div class="value"><?php echo e($rataKuis === null ? '-' : number_format($rataKuis, 1)); ?></div></td>
            <td><div class="label"><?php echo e(__('guru_analitik.nilai.rata_tugas')); ?></div><div class="value"><?php echo e($rataTugas === null ? '-' : number_format($rataTugas, 1)); ?></div></td>
            <td><div class="label"><?php echo e(__('guru_analitik.nilai.rata_total')); ?></div><div class="value"><?php echo e($rataTotal === null ? '-' : number_format($rataTotal, 1)); ?></div></td>
            <td><div class="label"><?php echo e(__('guru_analitik.nilai.nilai_terisi')); ?></div><div class="value"><?php echo e($jumlahDinilai); ?></div></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="center" style="width: 24px;"><?php echo e(__('guru_analitik.label.no')); ?></th>
                <th style="width: 50px;"><?php echo e(__('guru_analitik.label.type')); ?></th>
                <th><?php echo e(__('guru_analitik.label.title')); ?></th>
                <th style="width: 110px;"><?php echo e(__('guru_analitik.label.class')); ?></th>
                <th style="width: 120px;"><?php echo e(__('guru_analitik.label.meeting')); ?></th>
                <th class="center" style="width: 55px;"><?php echo e(__('guru_analitik.label.grade')); ?></th>
                <th style="width: 80px;"><?php echo e(__('guru_analitik.label.date')); ?></th>
                <th style="width: 70px;"><?php echo e(__('guru_analitik.label.notes')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="center"><?php echo e($i + 1); ?></td>
                    <td><?php echo e($row['jenis']); ?></td>
                    <td><?php echo e($row['judul']); ?></td>
                    <td><?php echo e($row['kelas'] ?? '-'); ?></td>
                    <td><?php echo e($row['pertemuan'] ?? '-'); ?></td>
                    <td class="center"><?php echo e($row['nilai'] === null ? '-' : number_format($row['nilai'], 1)); ?></td>
                    <td><?php echo e($row['tanggal']?->format('d/m/Y') ?? '-'); ?></td>
                    <td><?php echo e($row['catatan'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="center muted"><?php echo e(__('guru_analitik.nilai.no_score_for_student')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer"><?php echo e(__('guru_analitik.nilai.pdf.footer')); ?></div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\SIMBA\resources\views\guru\nilai\pdf.blade.php ENDPATH**/ ?>