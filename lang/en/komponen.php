<?php

return [
    // components/back-link.blade.php
    'kembali' => 'Back',

    // components/empty-state.blade.php
    'tidak_ada_data' => 'No data.',

    // components/form-select.blade.php
    'pilih_opsi' => '-- Select --',

    // components/loading-overlay.blade.php
    'ai_sedang_menyusun' => 'AI is composing',
    'catatan_waktu_proses' => 'This process may take 1-3 minutes depending on the number of questions and the speed of the AI server.',
    'mohon_tunggu' => 'Please wait and do not close this page...',

    // components/konteks-soal-picker.blade.php
    'konteks_materi' => 'Material Context (optional)',
    'kembalikan_ke_pertemuan' => 'Back to this meeting',
    'bantuan_konteks_materi' => 'Tick the meetings whose materials you want to use as a reference. Tick specific materials when only some of them are used; materials left unticked in other meetings are still used. The AI takes the full text of the selected materials automatically, so there is no passage to pick.',
    'konteks_tidak_ada_pertemuan' => 'There are no other meetings to choose from yet. The context uses the materials of this meeting.',
    'konteks_dipakai' => 'Context used:',
    'batas_pilihan_konteks' => 'Limit per question set: :pertemuan meetings, :materi materials.',
    'tampilkan_materi' => 'Show or hide the materials of this meeting',
    'pilih_semua_materi' => 'Tick all materials',
    'lepaskan_semua_materi' => 'Untick all materials',
    'materi_dicentang' => 'materials ticked',
    'materi_kosong_pertemuan' => 'Left unticked, every material of this meeting is used.',
    'materi_tanpa_dokumen' => 'no indexed document yet',
    'konteks_pesan' => [
        'pakai_pertemuan' => 'Using the materials of this meeting.',
        'materi_dipilih' => ':count materials selected',
        'semua_materi' => 'all materials',
    ],

    // components/materi-utama-select.blade.php
    'materi_utama' => 'Primary Material',
    'opsional' => '(optional)',
    'tanpa_materi_utama' => '-- No primary material --',
    'bantuan_materi_utama' => 'The material most directly assessed by this quiz. Only from the meeting above.',
    'materi_pendukung' => 'Supporting Material',
    'opsional_boleh_banyak' => '(optional, multiple allowed & may be from other meetings)',
    'bantuan_materi_pendukung' => 'Other materials that are also used as a question source. Their scores are attributed to this material as well, not only to the primary material.',
];
