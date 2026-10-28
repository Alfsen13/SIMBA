<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Validasi materi_id pada form kuis & tugas.
 *
 * Kolom ini menentukan topik lemah mana yang ditindak lanjuti remedial, jadi
 * guru tidak boleh bisa menunjuk materi milik kelas orang lain.
 */
function materiUtamaBuatKelas(User $guru, string $nama): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id, 'nama_kelas' => $nama]);
}

function materiUtamaBuatMateri(Kelas $kelas, User $guru, Pertemuan $pertemuan, string $judul): Materi
{
    return Materi::factory()->create([
        'judul' => $judul,
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
}

function materiUtamaBuatPertemuan(Kelas $kelas, string $nama): Pertemuan
{
    return Pertemuan::create(['kelas_id' => $kelas->id, 'nama_pertemuan' => $nama, 'urutan' => 1]);
}

function materiUtamaPayload(Kelas $kelas, Pertemuan $pertemuan, ?int $materiId, array $materiIds = []): array
{
    return [
        'judul' => 'Kuis Uji Materi Utama',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'materi_id' => $materiId,
        'materi_ids' => $materiIds,
        'soal' => json_encode([['pertanyaan' => 'Soal.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]]),
        'durasi_menit' => 30,
    ];
}

test('kuis menerima materi milik kelas yang diajar guru', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Sendiri');
    $pertemuan = materiUtamaBuatPertemuan($kelas, 'P1');
    $materi = materiUtamaBuatMateri($kelas, $guru, $pertemuan, 'Materi Sendiri');

    $response = $this->actingAs($guru)
        ->post(route('pengajar.kuis.store'), materiUtamaPayload($kelas, $pertemuan, $materi->id));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('kuis', ['materi_id' => $materi->id]);
});

test('kuis menolak materi milik kelas guru lain', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $guruLain = User::factory()->pengajar()->verified()->create();

    $kelasGuru = materiUtamaBuatKelas($guru, 'Kelas Sendiri');
    $pertemuan = materiUtamaBuatPertemuan($kelasGuru, 'P1');

    $kelasOrangLain = materiUtamaBuatKelas($guruLain, 'Kelas Orang Lain');
    $materiOrangLain = materiUtamaBuatMateri(
        $kelasOrangLain,
        $guruLain,
        materiUtamaBuatPertemuan($kelasOrangLain, 'P1'),
        'Materi Orang Lain'
    );

    $response = $this->actingAs($guru)
        ->post(route('pengajar.kuis.store'), materiUtamaPayload($kelasGuru, $pertemuan, $materiOrangLain->id));

    $response->assertSessionHasErrors('materi_id');
    $this->assertDatabaseMissing('kuis', ['materi_id' => $materiOrangLain->id]);
});

test('kuis menolak materi utama dari pertemuan lain meski satu kelas', function () {
    // Materi utama harus milik pertemuan kuis itu sendiri. Kalau tidak
    // dibatasi, atribusi topiklemah menunjuk materi yang tidak diuji.
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Satu Pertemuan');
    $pertemuanKuis = materiUtamaBuatPertemuan($kelas, 'Pertemuan Kuis');
    $pertemuanLain = materiUtamaBuatPertemuan($kelas, 'Pertemuan Lain');
    $materiLain = materiUtamaBuatMateri($kelas, $guru, $pertemuanLain, 'Materi Pertemuan Lain');

    $response = $this->actingAs($guru)
        ->post(route('pengajar.kuis.store'), materiUtamaPayload($kelas, $pertemuanKuis, $materiLain->id));

    $response->assertSessionHasErrors('materi_id');
    $this->assertDatabaseMissing('kuis', ['materi_id' => $materiLain->id]);
});

test('materi pendukung boleh dari pertemuan lain milik kelas yang sama', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Konteks Lintas Pertemuan');
    $pertemuanKuis = materiUtamaBuatPertemuan($kelas, 'Pertemuan Kuis');
    $pertemuanLain = materiUtamaBuatPertemuan($kelas, 'Pertemuan Konteks');

    $materiUtama = materiUtamaBuatMateri($kelas, $guru, $pertemuanKuis, 'Materi Utama Kuis');
    $pendukungA = materiUtamaBuatMateri($kelas, $guru, $pertemuanLain, 'Materi Pendukung A');
    $pendukungB = materiUtamaBuatMateri($kelas, $guru, $pertemuanLain, 'Materi Pendukung B');

    $response = $this->actingAs($guru)->post(route('pengajar.kuis.store'), materiUtamaPayload(
        $kelas,
        $pertemuanKuis,
        $materiUtama->id,
        [$pendukungA->id, $pendukungB->id]
    ));

    $response->assertSessionHasNoErrors();

    $kuis = Kuis::where('materi_id', $materiUtama->id)->firstOrFail();

    expect($kuis->materiPendukung()->pluck('materi.id')->sort()->values()->all())
        ->toBe([$pendukungA->id, $pendukungB->id]);
});

test('materi utama tidak diduplikasi sebagai materi pendukung', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Tanpa Duplikat');
    $pertemuan = materiUtamaBuatPertemuan($kelas, 'P1');
    $materi = materiUtamaBuatMateri($kelas, $guru, $pertemuan, 'Materi Sama');

    $this->actingAs($guru)->post(route('pengajar.kuis.store'), materiUtamaPayload(
        $kelas,
        $pertemuan,
        $materi->id,
        [$materi->id, $materi->id]
    ))->assertSessionHasNoErrors();

    $this->assertDatabaseCount('kuis_materi', 0);
});

test('kuis menolak materi pendukung milik kelas guru lain', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $guruLain = User::factory()->pengajar()->verified()->create();

    $kelasGuru = materiUtamaBuatKelas($guru, 'Kelas Kuis Sendiri');
    $pertemuan = materiUtamaBuatPertemuan($kelasGuru, 'P1');

    $kelasOrangLain = materiUtamaBuatKelas($guruLain, 'Kelas Orang Lain');
    $materiOrangLain = materiUtamaBuatMateri(
        $kelasOrangLain,
        $guruLain,
        materiUtamaBuatPertemuan($kelasOrangLain, 'P1'),
        'Materi Orang Lain'
    );

    $response = $this->actingAs($guru)->post(
        route('pengajar.kuis.store'),
        materiUtamaPayload($kelasGuru, $pertemuan, null, [$materiOrangLain->id])
    );

    $response->assertSessionHasErrors('materi_ids.0');
    $this->assertDatabaseMissing('kuis_materi', ['materi_id' => $materiOrangLain->id]);
});

test('update kuis mengganti materi pendukung sesuai pilihan terbaru', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Update Pivot');
    $pertemuan = materiUtamaBuatPertemuan($kelas, 'P1');

    $lama = materiUtamaBuatMateri($kelas, $guru, $pertemuan, 'Pendukung Lama');
    $baru = materiUtamaBuatMateri($kelas, $guru, $pertemuan, 'Pendukung Baru');

    $kuis = Kuis::create([
        'judul' => 'Kuis Lama',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [['pertanyaan' => 'Soal.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
    ]);
    $kuis->materiPendukung()->sync([$lama->id]);

    $this->actingAs($guru)->put(
        route('pengajar.kuis.update', $kuis->id),
        materiUtamaPayload($kelas, $pertemuan, null, [$baru->id])
    )->assertSessionHasNoErrors();

    expect($kuis->materiPendukung()->pluck('materi.id')->all())->toBe([$baru->id])
        ->and(DB::table('kuis_materi')->where('materi_id', $lama->id)->count())->toBe(0);
});

test('materi_id kosong tetap valid supaya atribusi jatuh ke pertemuan', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Tanpa Materi Utama');
    $pertemuan = materiUtamaBuatPertemuan($kelas, 'P1');

    $response = $this->actingAs($guru)
        ->post(route('pengajar.kuis.store'), materiUtamaPayload($kelas, $pertemuan, null));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('kuis', ['kelas_id' => $kelas->id, 'materi_id' => null]);
});

test('tugas menolak materi milik kelas guru lain', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $guruLain = User::factory()->pengajar()->verified()->create();

    $kelasGuru = materiUtamaBuatKelas($guru, 'Kelas Tugas Sendiri');
    $pertemuan = materiUtamaBuatPertemuan($kelasGuru, 'P1');

    $kelasOrangLain = materiUtamaBuatKelas($guruLain, 'Kelas Tugas Orang Lain');
    $materiOrangLain = materiUtamaBuatMateri(
        $kelasOrangLain,
        $guruLain,
        materiUtamaBuatPertemuan($kelasOrangLain, 'P1'),
        'Materi Tugas Orang Lain'
    );

    $response = $this->actingAs($guru)->post(route('pengajar.tugas.store'), [
        'judul' => 'Tugas Uji',
        'kelas_id' => $kelasGuru->id,
        'pertemuan_id' => $pertemuan->id,
        'materi_id' => $materiOrangLain->id,
        'tipe' => 'upload',
    ]);

    $response->assertSessionHasErrors('materi_id');
});

test('tugas menerima materi milik kelas yang diajar guru', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Tugas_OK');
    $pertemuan = materiUtamaBuatPertemuan($kelas, 'P1');
    $materi = materiUtamaBuatMateri($kelas, $guru, $pertemuan, 'Materi Tugas OK');

    $response = $this->actingAs($guru)->post(route('pengajar.tugas.store'), [
        'judul' => 'Tugas Uji',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'materi_id' => $materi->id,
        'tipe' => 'upload',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('tugas', ['materi_id' => $materi->id]);
});

test('tugas menyimpan materi pendukung di tabel pivot', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Tugas Pivot');
    $pertemuanKuis = materiUtamaBuatPertemuan($kelas, 'Pertemuan Tugas');
    $pertemuanLain = materiUtamaBuatPertemuan($kelas, 'Pertemuan Konteks');
    $materiUtama = materiUtamaBuatMateri($kelas, $guru, $pertemuanKuis, 'Materi Utama Tugas');
    $pendukung = materiUtamaBuatMateri($kelas, $guru, $pertemuanLain, 'Materi Pendukung Tugas');

    $this->actingAs($guru)->post(route('pengajar.tugas.store'), [
        'judul' => 'Tugas Pivot',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuanKuis->id,
        'materi_id' => $materiUtama->id,
        'materi_ids' => [$pendukung->id],
        'tipe' => 'upload',
    ])->assertSessionHasNoErrors();

    $tugas = Tugas::where('materi_id', $materiUtama->id)->firstOrFail();

    expect($tugas->materiPendukung()->pluck('materi.id')->all())->toBe([$pendukung->id]);
});

test('materi utama dari pertemuan lain ditolak pada tugas', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = materiUtamaBuatKelas($guru, 'Kelas Tugas Same Meeting');
    $pertemuanTugas = materiUtamaBuatPertemuan($kelas, 'Pertemuan Tugas');
    $pertemuanLain = materiUtamaBuatPertemuan($kelas, 'Pertemuan Lain');
    $materiLain = materiUtamaBuatMateri($kelas, $guru, $pertemuanLain, 'Materi Meeting Lain');

    $this->actingAs($guru)->post(route('pengajar.tugas.store'), [
        'judul' => 'Tugas Salah Meeting',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuanTugas->id,
        'materi_id' => $materiLain->id,
        'tipe' => 'upload',
    ])->assertSessionHasErrors('materi_id');
});
