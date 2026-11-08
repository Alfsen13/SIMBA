<?php

use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\PengajuanPerubahanPendidikan;
use App\Models\ProfilPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $this->jalur = JalurPendidikan::create([
        'kode' => 'formal',
        'nama' => 'Formal',
        'urutan' => 1,
        'aktif' => true,
    ]);
    $this->jenjangSmp = JenjangPendidikan::create([
        'jalur_id' => $this->jalur->id,
        'kode' => 'smp',
        'nama' => 'SMP',
        'urutan' => 1,
        'aktif' => true,
    ]);
    $this->jenjangSma = JenjangPendidikan::create([
        'jalur_id' => $this->jalur->id,
        'kode' => 'sma',
        'nama' => 'SMA',
        'urutan' => 2,
        'aktif' => true,
    ]);
    $this->tingkatSmp = TingkatPendidikan::create([
        'jenjang_id' => $this->jenjangSmp->id,
        'kode' => 'kelas_9',
        'nama' => 'Kelas 9',
        'urutan' => 3,
        'aktif' => true,
    ]);
    $this->tingkatSma = TingkatPendidikan::create([
        'jenjang_id' => $this->jenjangSma->id,
        'kode' => 'kelas_10',
        'nama' => 'Kelas 10',
        'urutan' => 1,
        'aktif' => true,
    ]);

    ProfilPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
    ]);
});

test('operator education change request pages use selected locale and touch-sized controls', function () {
    $operator = User::factory()->operator()->verified()->create();
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'catatan_operator' => 'Saya ingin pindah ke jenjang SMA.',
    ]);

    $this->actingAs($operator)
        ->get(route('operator.pengajuan-perubahan-pendidikan.index'))
        ->assertOk()
        ->assertSee('Pengajuan Perubahan Pendidikan')
        ->assertSee('Filter Status')
        ->assertSee('Tinjau')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    $this->from(route('operator.pengajuan-perubahan-pendidikan.index'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.pengajuan-perubahan-pendidikan.index'));

    $this->get(route('operator.pengajuan-perubahan-pendidikan.index'))
        ->assertOk()
        ->assertSee('Education Change Requests')
        ->assertSee('Filter by status')
        ->assertSee('Requested destination')
        ->assertSee('Review');

    $this->get(route('operator.pengajuan-perubahan-pendidikan.show', $pengajuan))
        ->assertOk()
        ->assertSee('Review Education Change Request')
        ->assertSee('Student profile')
        ->assertSee('Level transfer')
        ->assertSee('Requested grade')
        ->assertSee('Approve and update profile')
        ->assertSee('Request revision')
        ->assertSee('Rejection reason (required)')
        ->assertSee('min-h-11', false);
});

test('operator education change request list explains empty results and offers filter reset', function () {
    $operator = User::factory()->operator()->verified()->create();

    $this->actingAs($operator)
        ->get(route('operator.pengajuan-perubahan-pendidikan.index'))
        ->assertOk()
        ->assertSee('Belum ada pengajuan perubahan pendidikan.')
        ->assertDontSee('Hapus filter status');

    $this->get(route('operator.pengajuan-perubahan-pendidikan.index', ['status' => 'approved']))
        ->assertOk()
        ->assertSee('Tidak ada pengajuan dengan status ini.')
        ->assertSee('Hapus filter status')
        ->assertSee('href="'.route('operator.pengajuan-perubahan-pendidikan.index').'"', false);
});

test('siswa tidak dapat mengajukan tingkat yang bukan bagian dari jenjang tujuan', function () {
    $this->actingAs($this->siswa)
        ->post(route('pelajar.pengajuan-pendidikan.store'), [
            'jenis' => 'perpindahan_jenjang',
            'jenjang_id' => $this->jenjangSmp->id,
            'tingkat_id' => $this->tingkatSma->id,
            'alasan' => 'Saya ingin memperbarui jenjang pendidikan.',
        ])
        ->assertSessionHasErrors('tingkat_id');

    expect($this->siswa->pengajuanPerubahanPendidikan()->exists())->toBeFalse();
});

test('siswa dapat mengajukan tingkat yang sesuai dengan jenjang tujuan', function () {
    $this->actingAs($this->siswa)
        ->post(route('pelajar.pengajuan-pendidikan.store'), [
            'jenis' => 'kenaikan_kelas',
            'jenjang_id' => $this->jenjangSmp->id,
            'tingkat_id' => $this->tingkatSmp->id,
            'alasan' => 'Saya ingin naik ke tingkat kelas sembilan.',
        ])
        ->assertRedirect(route('pelajar.pengajuan-pendidikan.index'))
        ->assertSessionHasNoErrors();

    $pengajuan = $this->siswa->pengajuanPerubahanPendidikan()->first();

    expect($pengajuan)->not->toBeNull()
        ->and($pengajuan->jenjang_id)->toBe($this->jenjangSmp->id)
        ->and($pengajuan->tingkat_id)->toBe($this->tingkatSmp->id)
        ->and($pengajuan->alasan)->toBe('Saya ingin naik ke tingkat kelas sembilan.')
        ->and($pengajuan->catatan_operator)->toBeNull();
});

test('alasan siswa tetap terpisah dari catatan revisi operator', function () {
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'alasan' => 'Saya ingin pindah ke jenjang SMA.',
    ]);
    $operator = User::factory()->operator()->verified()->create();

    $this->actingAs($operator)
        ->post(route('operator.pengajuan-perubahan-pendidikan.minta-revisi', $pengajuan), [
            'catatan_operator' => 'Mohon lengkapi dokumen pendukung.',
        ])->assertRedirect();

    expect($pengajuan->fresh()->alasan)->toBe('Saya ingin pindah ke jenjang SMA.')
        ->and($pengajuan->fresh()->catatan_operator)->toBe('Mohon lengkapi dokumen pendukung.');

    $this->actingAs($this->siswa)
        ->get(route('pelajar.pengajuan-pendidikan.show', $pengajuan))
        ->assertOk()
        ->assertSee('Alasan pengajuan')
        ->assertSee('Saya ingin pindah ke jenjang SMA.')
        ->assertSee('Catatan operator')
        ->assertSee('Mohon lengkapi dokumen pendukung.');
});

test('persetujuan tanpa catatan tidak mengubah alasan menjadi catatan operator', function () {
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'alasan' => 'Saya ingin pindah ke jenjang SMA.',
    ]);

    $this->actingAs(User::factory()->operator()->verified()->create())
        ->post(route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan), [])
        ->assertRedirect(route('operator.pengajuan-perubahan-pendidikan.index'));

    expect($pengajuan->fresh()->alasan)->toBe('Saya ingin pindah ke jenjang SMA.')
        ->and($pengajuan->fresh()->catatan_operator)->toBeNull();
    $this->actingAs($this->siswa)->get(route('pelajar.pengajuan-pendidikan.show', $pengajuan))
        ->assertOk()->assertSee('Saya ingin pindah ke jenjang SMA.')->assertDontSee('Catatan operator');
});

test('catatan lama yang asalnya tidak pasti tampil sekali dengan label netral', function () {
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenis' => 'kenaikan_kelas',
        'status' => 'rejected',
        'reviewed_at' => now(),
        'catatan_operator' => 'Catatan lama yang tersimpan.',
    ]);

    $response = $this->actingAs($this->siswa)->get(route('pelajar.pengajuan-pendidikan.show', $pengajuan));
    $response->assertOk()->assertSee('Catatan pengajuan terdahulu')
        ->assertDontSee('Catatan operator')->assertDontSee('Alasan pengajuan');
    expect(substr_count($response->getContent(), 'Catatan lama yang tersimpan.'))->toBe(1);
});

test('halaman pengajuan siswa menggunakan locale Inggris', function () {
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenis' => 'kenaikan_kelas',
        'status' => 'pending',
        'alasan' => 'Alasan pengajuan siswa.',
    ]);

    $this->actingAs($this->siswa)->post(route('locale.update'), ['locale' => 'en']);
    $this->get(route('pelajar.pengajuan-pendidikan.show', $pengajuan))
        ->assertOk()->assertSee('Education Change Request Details')
        ->assertSee('Request date')->assertSee('Reason for request');
});

test('migrasi memindahkan alasan lama yang belum ditinjau tanpa menebak catatan yang sudah ditinjau', function () {
    $migration = require database_path('migrations/2026_11_07_033138_add_alasan_to_pengajuan_perubahan_pendidikan_table.php');
    $migration->down();

    $belumDitinjau = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id, 'jalur_id' => $this->jalur->id,
        'jenis' => 'kenaikan_kelas', 'status' => 'pending', 'catatan_operator' => 'Alasan lama siswa',
    ]);
    $sudahDitinjau = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id, 'jalur_id' => $this->jalur->id,
        'jenis' => 'kenaikan_kelas', 'status' => 'approved', 'reviewed_at' => now(), 'catatan_operator' => 'Catatan lama',
    ]);

    $migration->up();
    expect($belumDitinjau->fresh()->alasan)->toBe('Alasan lama siswa')
        ->and($belumDitinjau->fresh()->catatan_operator)->toBeNull()
        ->and($sudahDitinjau->fresh()->alasan)->toBeNull()
        ->and($sudahDitinjau->fresh()->catatan_operator)->toBe('Catatan lama');
});

test('pengajuan aktif yang sudah ada mencegah pengiriman pengajuan kedua', function () {
    PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSmp->id,
        'tingkat_id' => $this->tingkatSmp->id,
        'jenis' => 'kenaikan_kelas',
        'status' => 'under_review',
        'catatan_operator' => 'Menunggu keputusan operator.',
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.pengajuan-pendidikan.store'), [
            'jenis' => 'kenaikan_kelas',
            'jenjang_id' => $this->jenjangSma->id,
            'tingkat_id' => $this->tingkatSma->id,
            'alasan' => 'Saya ingin mengajukan perubahan ke tingkat berikutnya.',
        ])
        ->assertRedirect(route('pelajar.pengajuan-pendidikan.index'))
        ->assertSessionHas('error', 'Masih ada pengajuan perubahan pendidikan yang sedang diproses.');

    expect($this->siswa->pengajuanPerubahanPendidikan()->count())->toBe(1);
});

test('persetujuan ulang yang statusnya berubah tidak memperbarui profil siswa', function () {
    $operator = User::factory()->operator()->verified()->create();
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'catatan_operator' => 'Saya ingin pindah ke jenjang SMA.',
    ]);

    $statusBerubahSesudahBinding = false;
    DB::listen(function (QueryExecuted $query) use ($pengajuan, &$statusBerubahSesudahBinding): void {
        $sql = strtolower($query->sql);

        if (! $statusBerubahSesudahBinding
            && str_starts_with(ltrim($sql), 'select')
            && str_contains($sql, 'pengajuan_perubahan_pendidikan')
            && str_contains($sql, 'limit 1')) {
            $statusBerubahSesudahBinding = true;
            DB::table('pengajuan_perubahan_pendidikan')
                ->where('id', $pengajuan->id)
                ->update(['status' => 'rejected']);
        }
    });

    $this->actingAs($operator)
        ->post(route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan), [
            'catatan_operator' => '',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Pengajuan ini sudah diproses sebelumnya.');

    expect($statusBerubahSesudahBinding)->toBeTrue()
        ->and($pengajuan->fresh()->status)->toBe('rejected')
        ->and($this->siswa->profilPendidikan->jenjang_id)->toBeNull();
});

test('operator tidak dapat menyetujui tujuan pendidikan yang sudah tidak tersedia', function (string $perubahan) {
    $operator = User::factory()->operator()->verified()->create();
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'catatan_operator' => 'Saya ingin pindah ke jenjang SMA.',
    ]);

    if ($perubahan === 'jenjang_dihapus') {
        $pengajuan->update(['jenjang_id' => null]);
    } else {
        $this->tingkatSma->update(['aktif' => false]);
    }

    $this->actingAs($operator)
        ->post(route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan))
        ->assertRedirect()
        ->assertSessionHas('error', 'Tujuan pendidikan pada pengajuan sudah tidak aktif atau tidak valid. Profil siswa tidak diubah.');

    expect($pengajuan->fresh()->status)->toBe('pending')
        ->and($this->siswa->profilPendidikan->jenjang_id)->toBeNull()
        ->and($this->siswa->profilPendidikan->tingkat_id)->toBeNull();
})->with([
    'jenjang telah dihapus' => 'jenjang_dihapus',
    'tingkat telah dinonaktifkan' => 'tingkat_dinonaktifkan',
]);
