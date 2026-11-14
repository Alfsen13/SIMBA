<?php

return [
    // components/back-link.blade.php
    'kembali' => 'Kembali',

    // components/empty-state.blade.php
    'tidak_ada_data' => 'Tidak ada data.',

    // components/form-select.blade.php
    'pilih_opsi' => '-- Pilih --',

    // components/loading-overlay.blade.php
    'ai_sedang_menyusun' => 'AI sedang menyusun',
    'catatan_waktu_proses' => 'Proses ini bisa memakan waktu 1-3 menit tergantung jumlah soal dan kecepatan server AI.',
    'mohon_tunggu' => 'Mohon tunggu dan jangan menutup halaman ini...',

    // components/konteks-soal-picker.blade.php
    'konteks_materi' => 'Konteks Materi (opsional)',
    'kembalikan_ke_pertemuan' => 'Kembalikan ke pertemuan ini',
    'bantuan_konteks_materi' => 'Centang pertemuan yang materinya mau dipakai sebagai acuan. Centang materi tertentu bila hanya sebagian yang dipakai; materi yang tidak dicentang di pertemuan lain tetap ikut dipakai. Seluruh isi dokumen materi terpilih dipakai AI otomatis, jadi tidak ada potongan yang perlu dipilih.',
    'konteks_tidak_ada_pertemuan' => 'Belum ada pertemuan lain yang bisa dipilih. Konteks memakai materi pertemuan ini.',
    'konteks_dipakai' => 'Konteks yang dipakai:',
    'batas_pilihan_konteks' => 'Batas per susunan soal: :pertemuan pertemuan, :materi materi.',
    'tampilkan_materi' => 'Tampilkan atau sembunyikan daftar materi pertemuan ini',
    'pilih_semua_materi' => 'Pilih semua materi',
    'lepaskan_semua_materi' => 'Lepas semua materi',
    'materi_dicentang' => 'materi dicentang',
    'materi_kosong_pertemuan' => 'Tanpa centang, seluruh materi pertemuan ini dipakai.',
    'materi_tanpa_dokumen' => 'belum ada dokumen terindeks',
    'konteks_pesan' => [
        'pakai_pertemuan' => 'Memakai materi pertemuan ini.',
        'materi_dipilih' => ':count materi dipilih',
        'semua_materi' => 'seluruh materi',
    ],

    // components/materi-utama-select.blade.php
    'materi_utama' => 'Materi Utama',
    'opsional' => '(opsional)',
    'tanpa_materi_utama' => '-- Tanpa materi utama --',
    'bantuan_materi_utama' => 'Materi yang paling utama diuji kuis ini. Hanya boleh dari pertemuan di atas.',
    'materi_pendukung' => 'Materi Pendukung',
    'opsional_boleh_banyak' => '(opsional, boleh banyak & dari pertemuan lain)',
    'bantuan_materi_pendukung' => 'Materi lain yang ikut jadi sumber soal. Nilainya ikut diatribusikan ke materi ini, bukan cuma ke materi utama.',
];
