<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->enum('provider', ['ollama', 'gemini', 'nvidia_nim', 'openai', 'anthropic', 'custom'])->default('ollama');
            $table->string('endpoint')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->json('options')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('tested_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ai_settings');
    }
};
