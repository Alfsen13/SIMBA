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
    'batas_pilihan_konteks' => 'Limit per question set: :pertemuan meetings, :materi materials, :potongan document excerpts.',
    'tampilkan_materi' => 'Show or hide the materials of this meeting',
    'pilih_semua_materi' => 'Tick all materials',
    'lepaskan_semua_materi' => 'Untick all materials',
    'jumlah_materi' => ':terpilih of :total materials ticked',
    'materi_dicentang' => 'materials ticked',
    'materi_kosong_pertemuan' => 'Left unticked, every material of this meeting is used.',
    'materi_tanpa_dokumen' => 'no indexed document yet',
    'potongan_dokumen' => 'Document excerpts',
    'potongan_dokumen_singkat' => 'Pick the text the AI may use.',
    'potongan_estimasi_lebih' => 'The selected text is estimated above the character budget, so some excerpts will be cut when the prompt is built.',
    'potongan_dokumen_bantu' => 'Tick specific text passages so regenerating uses the same reference. Once any passage is ticked, questions are built only from those passages and similarity search is skipped.',
    'muat_potongan' => 'Loading excerpts...',
    'potongan_kosong' => 'The materials in the ticked meetings have no indexed document yet.',
    'potongan_tanpa_pertemuan' => 'Tick at least one meeting first, then open this list.',
    'topik_potongan' => 'Topic for auto-fill',
    'topik_potongan_placeholder' => 'e.g. chain rule',
    'pilih_halaman' => 'Tick this page',
    'lepaskan_potongan' => 'Untick all excerpts',
    'isi_otomatis' => 'Auto-fill',
    'isi_otomatis_sibuk' => 'Matching topic...',
    'halaman_sebelumnya' => 'Previous',
    'halaman_berikutnya' => 'Next',
    'konteks_chunk_pesan' => [
        'batas' => 'The :max excerpt limit is reached. Untick one first.',
        'halaman_penuh' => 'This page cannot add more until the :max limit is reached.',
        'tersimpan_lebih' => 'The saved selection holds more than :max excerpts; the rest were unticked.',
        'terpotong' => ':count matched excerpts were left out because of the :max limit.',
        'tanpa_hasil' => 'No excerpt matches that topic.',
        'tanpa_kata' => 'Write a topic first.',
        'tanpa_pertemuan' => 'Tick at least one meeting to load its excerpts.',
        'gagal_muat' => 'The excerpt list failed to load. Open it again.',
        'hilang' => ':count selected excerpts are no longer in the list (the document was re-indexed).',
        'pakai_pertemuan' => 'Using the materials of this meeting.',
        'materi_dipilih' => ':count materials selected',
        'semua_materi' => 'all materials',
        'potongan_dipilih' => ':count excerpts selected',
        'halaman' => 'Page :halaman of :total',
        'estimasi' => 'Estimated :estimasi of a :kuota character budget',
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
