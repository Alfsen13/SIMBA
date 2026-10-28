<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['pengajar', 'pelajar', 'operator', 'pengelola_operator'])->default('pelajar');
            $table->string('kategori_mapel')->nullable()->after('role');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('nip')->nullable()->unique();
            $table->string('nisn')->nullable()->unique();

            $table->index('role');
            $table->index('status');
            $table->foreign('verified_by')->references('id')->on('users');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
        });

        Schema::dropIfExists('users');
    }
};
