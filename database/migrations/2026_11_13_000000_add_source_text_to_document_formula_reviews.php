<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_formula_reviews', function (Blueprint $table) {
            // Teks lapisan PDF milik region: penanda untuk menambal chunk hasil
            // ekstraksi pertama saat rumus disetujui.
            $table->text('source_text')->nullable()->after('image_base64');

            // Chunk yang sudah ditambal. Array, bukan satu FK, karena chunk yang
            // bertetangga bisa memuat teks region yang sama (hasil overlap).
            $table->json('repaired_chunk_ids')->nullable()->after('document_chunk_id');
        });
    }

    public function down(): void
    {
        Schema::table('document_formula_reviews', function (Blueprint $table) {
            $table->dropColumn(['source_text', 'repaired_chunk_ids']);
        });
    }
};
