<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuis', function (Blueprint $table) {
            // Potongan dokumen yang dipakai saat generate soal, supaya generate
            // ulang memakai konteks yang sama dan halaman edit bisa menunjukkannya.
            $table->json('konteks_soal')->nullable()->after('soal');
        });
    }

    public function down(): void
    {
        Schema::table('kuis', function (Blueprint $table) {
            $table->dropColumn('konteks_soal');
        });
    }
};
