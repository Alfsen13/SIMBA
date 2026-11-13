<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_formula_reviews', function (Blueprint $table) {
            $table->string('status', 16)->default('pending')->after('latex');
            $table->text('ocr_error')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
        });

        Schema::table('document_formula_reviews', function (Blueprint $table) {
            // Region tetap disimpan saat OCR gagal, dengan alasan di `ocr_error`.
            $table->text('latex')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('document_formula_reviews', function (Blueprint $table) {
            $table->dropColumn(['status', 'ocr_error', 'rejected_at']);
        });
    }
};
