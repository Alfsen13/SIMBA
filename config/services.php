<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent'),
        'short_timeout' => env('GEMINI_SHORT_TIMEOUT', 30),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001'),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'simba-tutor'),
        'fallback_model_1' => env('OLLAMA_MODEL_FALLBACK_1', 'qwen2.5-coder:1.5b-base'),
        'fallback_model_2' => env('OLLAMA_MODEL_FALLBACK_2', 'qwen2.5:7b'),
        'embed_model' => env('OLLAMA_EMBED_MODEL', 'nomic-embed-text'),
        'num_ctx' => env('OLLAMA_NUM_CTX', 16384),
        'num_predict' => env('OLLAMA_NUM_PREDICT', 2048),
        'temperature' => env('OLLAMA_TEMPERATURE', 0.2),
    ],

    'embedding' => [
        'provider' => env('EMBEDDING_PROVIDER', 'ollama'),
    ],

    'pdf' => [
        'python_path' => env('PYTHON_PATH', 'py -X utf8'),
        'timeout' => env('PDF_EXTRACT_TIMEOUT', 600),
        'formula_ocr_enabled' => env('PDF_FORMULA_OCR_ENABLED', true),
        'formula_ocr_model' => env('PDF_FORMULA_OCR_MODEL', 'qwen3.5:4b'),
        'formula_ocr_budget' => (int) env('PDF_FORMULA_OCR_BUDGET', 180),
    ],

    'ml_service' => [
        'endpoint' => env('ML_SERVICE_ENDPOINT', 'http://localhost:5000'),
        'api_key' => env('ML_SERVICE_API_KEY'),
    ],

    'rag' => [
        'chunk_size' => env('RAG_CHUNK_SIZE', 1000),
        'chunk_overlap' => env('RAG_CHUNK_OVERLAP', 200),
        'top_k' => env('RAG_TOP_K', 3),
        'min_score' => env('RAG_MIN_SCORE', 0.4),
    ],

    'web_search' => [
        'enabled' => env('WEB_SEARCH_ENABLED', true),
        'timeout' => env('WEB_SEARCH_TIMEOUT', 5),
        'max_results' => env('WEB_SEARCH_MAX_RESULTS', 3),
        'google_api_key' => env('GOOGLE_CSE_API_KEY'),
        'google_cx' => env('GOOGLE_CSE_CX'),
    ],

    'symbols' => [
        'enabled' => env('SYMBOLS_ENABLED', true),
        'top_k' => env('SYMBOLS_TOP_K', 6),
        'min_score' => env('SYMBOLS_MIN_SCORE', 1.0),
        'max_chars' => env('SYMBOLS_MAX_CHARS', 2400),
        'formula_min_chars' => env('SYMBOLS_FORMULA_MIN_CHARS', 6),
        'formula_match_threshold' => env('SYMBOLS_FORMULA_MATCH_THRESHOLD', 0.9),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kuis Generator Retrieval
    |--------------------------------------------------------------------------
    |
    | Berapa banyak chunk dokumen yang diambil lewat RAG semantik saat menyusun
    | soal kuis. Kalau retrieval kosong (dokumen belum di-embed), service
    | otomatis jatuh ke ringkasan bab, lalu ke 6 chunk awal dokumen.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Taksonomi Bloom
    |--------------------------------------------------------------------------
    |
    | Dataset lengkap Taksonomi Bloom Revisi (definisi, kata kerja operasional,
    | pola pertanyaan, contoh klasifikasi, dan panduan kata kerja ambigu) ada
    | di database/data/bloom.json dan dibaca oleh BloomTaxonomyService.
    |
    | Konfigurasi di sini hanya controlling perilaku generator soal, bukan
    | isi taksonomi-nya: apakah level Bloom disertakan ke prompt dan disimpan,
    | dan seberapa resmi heuristik lokal boleh mengoreksi level dari AI.
    |
    */
    'bloom' => [
        'enabled' => env('BLOOM_ENABLED', true),
        // Porsi skor heuristik lokal minimum sebelum boleh menimpa level AI.
        'min_confidence' => (float) env('BLOOM_MIN_CONFIDENCE', 0.34),
    ],

    'kuis_rag' => [
        'enabled' => env('KUIS_RAG_ENABLED', true),
        'top_k' => env('KUIS_RAG_TOP_K', 4),
        'min_score' => env('KUIS_RAG_MIN_SCORE', 0.35),
        'max_chars' => env('KUIS_RAG_MAX_CHARS', 4000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Remedial
    |--------------------------------------------------------------------------
    |
    | `cooldown_hari` = berapa hari minimal sebelum remedial yang sama boleh
    | dibuat ulang untuk siswa yang masih berisiko tinggi.
    |
    | Tanpa ini, tiap kali prediksi atau penilaian memicu remedial, baris
    | remedial BARU dibuat untuk siswa yang sama selama dia belum naik kelas,
    | jadi halaman remedial siswa dan kotak masuk guru membengkak tanpa batas.
    | Ada dua lapis pengaman: cooldown ini, dan "lewat kalau masih ada remedial
    | yang belum selesai".
    |
    | Set 0 untuk menonaktifkan cooldown (layer "belum selesai" tetap aktif).
    |
    | `ambang_pertemuan` = batas skor pertemuan (0-100). Bila skor pertemuan
    | siswa di bawah nilai ini, remedial khusus pertemuan itu dijadwalkan.
    | Hanya komponen yang sudah dinilai yang ikut dihitung.
    |
    | `bobot_tugas` / `bobot_kuis` = kontribusi rata-rata tugas dan kuis saat
    | pertemuan punya keduanya (default 80% tugas, 20% kuis).
    |
    */
    'remedial' => [
        'cooldown_hari' => (int) env('REMEDIAL_COOLDOWN_HARI', 7),
        'ambang_pertemuan' => (float) env('REMEDIAL_AMBANG_PERTEMUAN', 75),
        'bobot_tugas' => (float) env('REMEDIAL_BOBOT_TUGAS', 0.8),
        'bobot_kuis' => (float) env('REMEDIAL_BOBOT_KUIS', 0.2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prediksi Risiko (Skor Penyimpangan dari Baseline)
    |--------------------------------------------------------------------------
    |
    | Ambang kategori skor penyimpangan (0-100). Skor < ambang_aman = aman,
    | < ambang_pantau = perlu_pantau, sisanya berisiko_tinggi.
    |
    */
    'prediksi' => [
        'ambang_aman' => (float) env('PREDIKSI_AMBANG_AMAN', 20),
        'ambang_pantau' => (float) env('PREDIKSI_AMBANG_PANTAU', 40),
    ],

];
