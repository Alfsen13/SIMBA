<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Application Locale
    |--------------------------------------------------------------------------
    |
    | Locale yang dipakai bila session belum menyimpan pilihan pengguna.
    | Diambil dari APP_LOCALE (default "id" karena UI asli berbahasa Indonesia).
    |
    */

    'default' => env('APP_LOCALE', 'id'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | Daftar bahasa yang boleh dipilih pengguna. Key adalah kode locale yang
    | dipakai `app()->setLocale()` dan folder di `lang/`. `nllb`/`ai_name`
    | dipakai untuk menyusun instruksi bahasa pada prompt AI.
    |
    */

    'supported' => [
        'id' => [
            'label' => 'Bahasa Indonesia',
            'short' => 'ID',
            'ai_name' => 'Bahasa Indonesia',
            'ai_instruction' => 'Jawab SELALU dalam Bahasa Indonesia yang baik dan benar.',
        ],
        'en' => [
            'label' => 'English',
            'short' => 'EN',
            'ai_name' => 'English',
            'ai_instruction' => 'Always respond in clear, correct English.',
        ],
    ],

];
