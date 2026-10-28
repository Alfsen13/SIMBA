<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumpulan_tugas', function (Blueprint $table) {
            $table->timestamp('dimulai_pada')->nullable()->after('terlambat');
            $table->timestamp('batas_kerjakan')->nullable()->after('dimulai_pada');
        });
    }

    public function down(): void
    {
        Schema::table('pengumpulan_tugas', function (Blueprint $table) {
            $table->dropColumn(['dimulai_pada', 'batas_kerjakan']);
        });
    }
};
