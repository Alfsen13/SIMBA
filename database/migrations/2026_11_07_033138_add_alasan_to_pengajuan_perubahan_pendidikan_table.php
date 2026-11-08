<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_perubahan_pendidikan', function (Blueprint $table) {
            $table->text('alasan')->nullable();
        });

        DB::table('pengajuan_perubahan_pendidikan')
            ->whereIn('status', ['pending', 'under_review'])
            ->whereNull('reviewed_at')
            ->update(['alasan' => DB::raw('catatan_operator'), 'catatan_operator' => null]);
    }

    public function down(): void
    {
        DB::table('pengajuan_perubahan_pendidikan')
            ->whereNull('catatan_operator')
            ->update(['catatan_operator' => DB::raw('alasan')]);

        Schema::table('pengajuan_perubahan_pendidikan', function (Blueprint $table) {
            $table->dropColumn('alasan');
        });
    }
};
