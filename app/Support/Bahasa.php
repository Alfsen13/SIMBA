<?php

namespace App\Support;

class Bahasa
{

    public static function aiName(): string
    {
        $locale = app()->getLocale();

        return (string) config("locales.supported.{$locale}.ai_name", $locale);
    }

    public static function instruksi(): string
    {
        $locale = app()->getLocale();

        return (string) config(
            "locales.supported.{$locale}.ai_instruction",
            'Jawab SELALU dalam Bahasa Indonesia yang baik dan benar.',
        );
    }
}
