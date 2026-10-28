<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('jalur_id')->constrained('jalur_pendidikan');
            $table->foreignId('jenjang_id')->nullable()->constrained('jenjang_pendidikan')->nullOnDelete();
            $table->foreignId('tingkat_id')->nullable()->constrained('tingkat_pendidikan')->nullOnDelete();
            $table->string('institusi')->nullable();
            $table->string('program')->nullable();
            $table->text('minat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_pendidikan');
    }
};
