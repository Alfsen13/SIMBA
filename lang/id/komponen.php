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
    'rag_top_k' => 'Maksimal potongan dokumen per pertemuan (k)',
    'rag_top_k_default' => 'Bawaan sistem (:count)',
    'rag_top_k_help' => 'Jumlah yang dipakai bisa lebih sedikit karena relevansi dan batas panjang konteks. Nilai ini bukan ukuran akurasi.',
    'kembalikan_ke_pertemuan' => 'Kembalikan ke pertemuan ini',
    'bantuan_konteks_materi' => 'Centang pertemuan yang materinya mau dipakai sebagai acuan. Centang materi tertentu bila hanya sebagian yang dipakai; materi yang tidak dicentang di pertemuan lain tetap ikut dipakai.',
    'konteks_tidak_ada_pertemuan' => 'Belum ada pertemuan lain yang bisa dipilih. Konteks memakai materi pertemuan ini.',
    'konteks_dipakai' => 'Konteks yang dipakai:',
    'batas_pilihan_konteks' => 'Batas per susunan soal: :pertemuan pertemuan, :materi materi, :potongan potongan dokumen.',
    'tampilkan_materi' => 'Tampilkan atau sembunyikan daftar materi pertemuan ini',
    'pilih_semua_materi' => 'Pilih semua materi',
    'lepaskan_semua_materi' => 'Lepas semua materi',
    'jumlah_materi' => ':terpilih dari :total materi dicentang',
    'materi_dicentang' => 'materi dicentang',
    'materi_kosong_pertemuan' => 'Tanpa centang, seluruh materi pertemuan ini dipakai.',
    'materi_tanpa_dokumen' => 'belum ada dokumen terindeks',
    'potongan_dokumen' => 'Potongan dokumen',
    'potongan_dokumen_singkat' => 'Pilih teks yang boleh dipakai AI.',
    'potongan_estimasi_lebih' => 'Estimasi teks terpilih melebihi kuota karakter, jadi sebagian potongan akan dipotong saat prompt disusun.',
    'potongan_dokumen_bantu' => 'Centang potongan teks tertentu supaya generate ulang memakai acuan yang sama. Begitu ada yang dicentang, soal disusun hanya dari potongan itu dan pencarian mirip dilewati.',
    'muat_potongan' => 'Memuat daftar potongan...',
    'potongan_kosong' => 'Materi di pertemuan yang dicentang belum punya dokumen terindeks.',
    'potongan_tanpa_pertemuan' => 'Centang minimal satu pertemuan dulu, lalu buka daftar ini.',
    'topik_potongan' => 'Topik untuk isi otomatis',
    'topik_potongan_placeholder' => 'mis. aturan rantai',
    'isi_otomatis' => 'Isi otomatis',
    'isi_otomatis_sibuk' => 'Mencocokkan topik...',
    'pilih_halaman' => 'Centang halaman ini',
    'lepaskan_potongan' => 'Lepas semua potongan',
    'halaman_sebelumnya' => 'Sebelumnya',
    'halaman_berikutnya' => 'Berikutnya',
    'konteks_chunk_pesan' => [
        'batas' => 'Kuota :max potongan sudah terisi. Lepas salah satu dulu.',
        'halaman_penuh' => 'Halaman ini tidak bisa menambah sampai kuota :max terisi.',
        'tersimpan_lebih' => 'Pilihan tersimpan lebih dari :max potongan, sisanya dilepas.',
        'terpotong' => ':count potongan hasil pencocokan tidak ikut dipilih karena kuota :max.',
        'tanpa_hasil' => 'Tidak ada potongan yang cocok dengan topik itu.',
        'tanpa_kata' => 'Tulis topik terlebih dahulu.',
        'tanpa_pertemuan' => 'Centang minimal satu pertemuan untuk memuat potongannya.',
        'gagal_muat' => 'Daftar potongan gagal dimuat. Coba buka lagi.',
        'hilang' => ':count potongan terpilih sudah tidak ada di daftar (dokumennya ditanam ulang).',
        'pakai_pertemuan' => 'Memakai materi pertemuan ini.',
        'materi_dipilih' => ':count materi dipilih',
        'semua_materi' => 'seluruh materi',
        'potongan_dipilih' => ':count potongan dipilih',
        'halaman' => 'Halaman :halaman dari :total',
        'estimasi' => 'Estimasi :estimasi dari kuota :kuota karakter',
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
