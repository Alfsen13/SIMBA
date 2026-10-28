<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbols', function (Blueprint $table) {
            $table->string('id', 120)->primary();
            $table->string('domain')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->string('symbol')->nullable();
            $table->string('latex')->nullable();
            $table->string('name')->nullable();
            $table->text('definition')->nullable();
            $table->text('meaning')->nullable();
            $table->text('formula')->nullable();
            $table->text('variables')->nullable();
            $table->string('unit')->nullable();
            $table->text('example')->nullable();
            $table->text('related_symbols')->nullable();
            $table->text('related_concepts')->nullable();
            $table->text('common_misunderstanding')->nullable();
            $table->text('keywords')->nullable();
            $table->longText('retrieval_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbols');
    }
};
