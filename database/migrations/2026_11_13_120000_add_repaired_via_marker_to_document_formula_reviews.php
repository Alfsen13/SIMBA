<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_formula_reviews', function (Blueprint $table) {
            // Cara chunk aslinya ditambal: lewat penanda region (teks hasil
            // ekstraksi dengan penanda) atau lewat teks sumber. Menolak harus
            // menulis kembali bentuk yang sama, tidak boleh menebak.
            $table->boolean('repaired_via_marker')->nullable()->after('repaired_chunk_ids');
        });
    }

    public function down(): void
    {
        Schema::table('document_formula_reviews', function (Blueprint $table) {
            $table->dropColumn('repaired_via_marker');
        });
    }
};
