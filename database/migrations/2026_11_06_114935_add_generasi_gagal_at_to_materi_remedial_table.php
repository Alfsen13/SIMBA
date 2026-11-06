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
        Schema::table('materi_remedial', function (Blueprint $table) {
            $table->timestamp('generasi_gagal_at')->nullable()->after('penilaian_gagal_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materi_remedial', function (Blueprint $table) {
            $table->dropColumn('generasi_gagal_at');
        });
    }
};
