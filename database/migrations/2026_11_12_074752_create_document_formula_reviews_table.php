<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('formula_ocr_status')->nullable();
            $table->text('formula_ocr_error')->nullable();
        });

        Schema::create('document_formula_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_chunk_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('page');
            $table->string('region_key', 64);
            $table->longText('image_base64');
            $table->text('latex');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'page', 'region_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_formula_reviews');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['formula_ocr_status', 'formula_ocr_error']);
        });
    }
};
