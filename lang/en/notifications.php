<?php

return [
    'risiko_tinggi' => [
        'judul' => 'High-Risk Student Detected',
        'pesan' => 'Student :nama has been detected at high risk (score: :skor). Remedial material has been created.',
    ],

    'remedial_tersedia' => [
        'judul' => 'Remedial Material Available',
        'pesan' => 'New remedial material is available for you. Study it soon to improve your understanding.',
    ],

    'pendaftaran_kelas' => [
        'disetujui_judul' => 'Class Registration Approved',
        'disetujui_pesan' => 'Your registration for class :kelas was approved by the operator.',
        'ditolak_judul' => 'Class Registration Rejected',
        'ditolak_pesan' => 'Your registration for class :kelas was rejected by the operator.',
    ],

    'pengajuan_mengajar' => [
        'disetujui_judul' => 'Teaching Application Approved',
        'disetujui_pesan' => 'Your application to teach class :kelas was approved by the operator. The class is now yours.',
        'ditolak_judul' => 'Teaching Application Rejected',
        'ditolak_pesan' => 'Your application to teach class :kelas was rejected by the operator. You may apply again with more complete material.',
    ],

    'generate_pertemuan_gagal' => [
        'judul' => 'Meeting Generation Failed',
        'pesan' => 'Generating meetings for class ":kelas" failed: :alasan',
    ],
];
