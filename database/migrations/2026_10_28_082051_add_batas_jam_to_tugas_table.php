<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->integer('batas_jam')->nullable()->after('deadline');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn('batas_jam');
        });
    }
};
