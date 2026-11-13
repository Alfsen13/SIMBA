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
    'rag_top_k' => 'Maximum document excerpts per meeting (k)',
    'rag_top_k_default' => 'System default (:count)',
    'rag_top_k_help' => 'Fewer excerpts may be used due to relevance and context length limits. This value does not measure accuracy.',
    'kembalikan_ke_pertemuan' => 'Back to this meeting',
    'bantuan_konteks_materi' => 'Tick the meetings whose materials you want to use as a reference. Tick specific materials when only some of them are used; materials left unticked in other meetings are still used.',
    'konteks_tidak_ada_pertemuan' => 'There are no other meetings to choose from yet. The context uses the materials of this meeting.',
    'konteks_dipakai' => 'Context used:',

    // components/materi-utama-select.blade.php
    'materi_utama' => 'Primary Material',
    'opsional' => '(optional)',
    'tanpa_materi_utama' => '-- No primary material --',
    'bantuan_materi_utama' => 'The material most directly assessed by this quiz. Only from the meeting above.',
    'materi_pendukung' => 'Supporting Material',
    'opsional_boleh_banyak' => '(optional, multiple allowed & may be from other meetings)',
    'bantuan_materi_pendukung' => 'Other materials that are also used as a question source. Their scores are attributed to this material as well, not only to the primary material.',
];
