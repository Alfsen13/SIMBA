<?php

/**
 * Alur pengajuan mengajar kelas: pengajar melampirkan bahan rencana ajar,
 * lalu operator menyetujui (kelas.guru_id terisi) atau menolak — termasuk
 * penolakan otomatis pengajuan lain saat satu pengajar ditetapkan.
 *
 * Tidak ada helper global di file ini (nama test unik seluruh suite).
 */

use App\Models\AplikasiPengajar;
use App\Models\Kelas;
use App\Models\PengajuanPengajarKelas;
use App\Models\User;
use App\Notifications\PengajuanMengajarDiverifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->operator = User::factory()->operator()->verified()->create(['kategori_mapel' => 'Matematika']);
    $this->kelas = Kelas::factory()->create(['guru_id' => null, 'kategori' => 'Matematika']);
});

function pengajuanBuatRow(Kelas $kelas, User $guru, array $extra = []): PengajuanPengajarKelas
{
    return PengajuanPengajarKelas::create(array_merge([
        'kelas_id' => $kelas->id,
        'user_id' => $guru->id,
        'rencana_ajar' => 'Mengajarkan materi dasar dengan urutan runtut, latihan bertahap, dan asesmen awal tiap pertemuan.',
        'status' => PengajuanPengajarKelas::STATUS_PENDING,
    ], $extra));
}

test('form pengajuan mengajar tampil untuk kelas tersedia', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.kelas.ajukan', $this->kelas))
        ->assertOk()
        ->assertSee('name="rencana_ajar"', false)
        ->assertSee('name="berkas"', false);
});

test('pengajar mengirim bahan rencana ajar beserta berkas', function () {
    Storage::fake('local');

    $this->actingAs($this->guru)
        ->post(route('pengajar.kelas.ajukan.store', $this->kelas), [
            'rencana_ajar' => 'Rencana mengajar aljabar dasar: bilangan bulat, pecahan, lalu persamaan linear satu variabel.',
            'berkas' => UploadedFile::fake()->create('silabus.pdf', 128, 'application/pdf'),
        ])
        ->assertRedirect(route('pengajar.kelas.index'));

    $row = PengajuanPengajarKelas::first();

    expect($row->status)->toBe('pending')
        ->and($row->user_id)->toBe($this->guru->id)
        ->and($row->berkas_path)->not->toBeNull();

    Storage::disk('local')->assertExists($row->berkas_path);

    // Kelas belum terisi — menunggu keputusan operator.
    expect($this->kelas->fresh()->guru_id)->toBeNull();
});

test('rencana ajar wajib diisi minimal 20 karakter', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.kelas.ajukan.store', $this->kelas), [
            'rencana_ajar' => 'Terlalu singkat.',
        ])
        ->assertSessionHasErrors('rencana_ajar');

    $this->assertDatabaseCount('pengajuan_pengajar_kelas', 0);
});

test('pengajuan ulang setelah ditolak mengubah baris yang sama', function () {
    pengajuanBuatRow($this->kelas, $this->guru, [
        'status' => PengajuanPengajarKelas::STATUS_DITOLAK,
        'catatan_operator' => 'Mohon lengkapi urutan materinya.',
    ]);

    $this->actingAs($this->guru)
        ->post(route('pengajar.kelas.ajukan.store', $this->kelas), [
            'rencana_ajar' => 'Revisi rencana: urutan materi dirinci per pertemuan dengan asesmen formatif di akhir tiap bab.',
        ])
        ->assertRedirect(route('pengajar.kelas.index'));

    $this->assertDatabaseCount('pengajuan_pengajar_kelas', 1);
    expect(PengajuanPengajarKelas::first()->status)->toBe('pending');
});

test('pengajuan kedua saat masih pending ditolak dengan pesan', function () {
    pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->guru)
        ->post(route('pengajar.kelas.ajukan.store', $this->kelas), [
            'rencana_ajar' => 'Upaya kedua mengirim rencana ajar dengan urutan materi yang berbeda untuk kelas yang sama.',
        ])
        ->assertSessionHas('error');

    $this->assertDatabaseCount('pengajuan_pengajar_kelas', 1);
});

test('operator melihat daftar pengajar pengaju beserta detail bahan ajar', function () {
    pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.show', $this->kelas))
        ->assertOk()
        ->assertSee($this->guru->name)
        ->assertSee('Mengajarkan materi dasar', false);
});

test('menyetujui pengajuan mengisi kelas dan otomatis menolak pengajuan lain', function () {
    Notification::fake();

    $pengajuA = pengajuanBuatRow($this->kelas, $this->guru);
    $pengajuB = pengajuanBuatRow($this->kelas, User::factory()->pengajar()->verified()->create());

    $this->actingAs($this->operator)
        ->post(route('operator.pengajuan-mengajar.setujui', $pengajuA))
        ->assertSessionHas('success');

    expect($this->kelas->fresh()->guru_id)->toBe($this->guru->id)
        ->and($pengajuA->fresh()->status)->toBe('disetujui')
        ->and($pengajuA->fresh()->reviewed_by)->toBe($this->operator->id)
        ->and($pengajuB->fresh()->status)->toBe('ditolak')
        ->and($pengajuB->fresh()->catatan_operator)->not->toBeNull();

    Notification::assertSentTo($this->guru, PengajuanMengajarDiverifikasi::class);
    Notification::assertSentTo(
        $pengajuB->pengajar,
        fn (PengajuanMengajarDiverifikasi $notification) => $notification->afterCommit === true,
    );
});

test('pengajuan kelas yang sudah terisi tidak bisa disetujui', function () {
    $pengajuan = pengajuanBuatRow($this->kelas, $this->guru);
    $this->kelas->update(['guru_id' => User::factory()->pengajar()->verified()->create()->id]);

    $this->actingAs($this->operator)
        ->post(route('operator.pengajuan-mengajar.setujui', $pengajuan))
        ->assertSessionHas('error');

    expect($pengajuan->fresh()->status)->toBe('pending');
});

test('operator menolak pengajuan dengan catatan', function () {
    Notification::fake();

    $pengajuan = pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->operator)
        ->post(route('operator.pengajuan-mengajar.tolak', $pengajuan), [
            'catatan' => 'Mohon lampirkan silabus yang lebih rinci.',
        ])
        ->assertSessionHas('success');

    expect($pengajuan->fresh()->status)->toBe('ditolak')
        ->and($pengajuan->fresh()->catatan_operator)->toBe('Mohon lampirkan silabus yang lebih rinci.');

    Notification::assertSentTo($this->guru, PengajuanMengajarDiverifikasi::class);
});

test('operator bisa mengunduh berkas rencana ajar', function () {
    Storage::fake('local');
    Storage::disk('local')->put('pengajuan/silabus.pdf', 'dummy-isi-pdf');

    $pengajuan = pengajuanBuatRow($this->kelas, $this->guru, ['berkas_path' => 'pengajuan/silabus.pdf']);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.berkas', $pengajuan))
        ->assertOk();
});

test('pengajar tidak bisa memproses keputusan operator', function () {
    $pengajuan = pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->guru)
        ->post(route('operator.pengajuan-mengajar.setujui', $pengajuan))
        ->assertRedirect();

    expect($this->kelas->fresh()->guru_id)->toBeNull();
});

test('pelajar tidak bisa membuka form pengajuan mengajar', function () {
    $pelajar = User::factory()->verified()->create();

    $this->actingAs($pelajar)
        ->get(route('pengajar.kelas.ajukan', $this->kelas))
        ->assertRedirect();
});

test('halaman tinjauan menampilkan profil pelamar dari pendaftaran', function () {
    AplikasiPengajar::create([
        'user_id' => $this->guru->id,
        'pendidikan_tertinggi' => 'S2 Pendidikan Matematika',
        'pengalaman_mengajar' => '5 tahun SMK',
        'kategori_mapel' => 'Matematika',
        'mata_pelajaran' => ['Aljabar', 'Statistika'],
        'jenjang_dan_tingkat' => 'SMA / Kelas XI',
    ]);
    pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.show', $this->kelas))
        ->assertOk()
        ->assertSee('S2 Pendidikan Matematika')
        ->assertSee('5 tahun SMK')
        ->assertSee('Aljabar')
        ->assertSee('SMA / Kelas XI');
});

test('halaman tinjauan menyebut pelamar tanpa profil pendaftaran', function () {
    pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.show', $this->kelas))
        ->assertOk()
        ->assertSee(__('operator.pengajuan_mengajar.profil_kosong'));
});

test('operator bisa mengunduh cv dan ijazah pelamar', function () {
    Storage::fake('public');
    Storage::disk('public')->put('aplikasi_pengajar/cv/cv.pdf', 'dummy-cv');
    Storage::disk('public')->put('aplikasi_pengajar/ijazah/ijazah.pdf', 'dummy-ijazah');

    AplikasiPengajar::create([
        'user_id' => $this->guru->id,
        'cv_path' => 'aplikasi_pengajar/cv/cv.pdf',
        'ijazah_path' => 'aplikasi_pengajar/ijazah/ijazah.pdf',
    ]);
    $pengajuan = pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'cv']))
        ->assertOk();

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'ijazah']))
        ->assertOk();
});

test('unduh profil berkas 404 untuk jenis tak dikenal atau berkas kosong', function () {
    $pengajuan = pengajuanBuatRow($this->kelas, $this->guru);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'cv']))
        ->assertNotFound();

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.profil-berkas', ['pengajuan' => $pengajuan, 'jenis' => 'ktp']))
        ->assertNotFound();
});

test('operator tidak dapat melihat atau mengelola pengajuan kelas kategori lain', function () {
    $kelasLain = Kelas::factory()->create([
        'guru_id' => null,
        'kategori' => 'Bahasa Inggris',
    ]);
    $pengajuan = pengajuanBuatRow($kelasLain, $this->guru, [
        'berkas_path' => 'pengajuan/silabus.pdf',
    ]);

    Storage::fake('local');
    Storage::disk('local')->put('pengajuan/silabus.pdf', 'private');
    Storage::fake('public');
    Storage::disk('public')->put('aplikasi_pengajar/cv/cv.pdf', 'private');
    AplikasiPengajar::create([
        'user_id' => $this->guru->id,
        'cv_path' => 'aplikasi_pengajar/cv/cv.pdf',
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.pengajuan-mengajar.show', $kelasLain))
        ->assertForbidden();

    $this->post(route('operator.pengajuan-mengajar.setujui', $pengajuan))
        ->assertForbidden();
    $this->post(route('operator.pengajuan-mengajar.tolak', $pengajuan), ['catatan' => 'Ditolak'])
        ->assertForbidden();
    $this->get(route('operator.pengajuan-mengajar.berkas', $pengajuan))
        ->assertForbidden();
    $this->get(route('operator.pengajuan-mengajar.profil-berkas', [
        'pengajuan' => $pengajuan,
        'jenis' => 'cv',
    ]))->assertForbidden();

    expect($pengajuan->fresh()->status)->toBe(PengajuanPengajarKelas::STATUS_PENDING)
        ->and($kelasLain->fresh()->guru_id)->toBeNull();
});
