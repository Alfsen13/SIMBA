<?php

use App\Models\Kelas;
use App\Models\MateriRemedial;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\Tugas;
use App\Models\User;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
});

test('guru dashboard returns 200', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.dashboard'))
        ->assertOk();
});

test('guru dashboard limits remedial and risk lists to students in their active classes', function () {
    $kelasGuru = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $siswaGuru = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswaGuru->kelasDiikuti()->attach($kelasGuru->id);

    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasGuruLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    $siswaGuruLain = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswaGuruLain->kelasDiikuti()->attach($kelasGuruLain->id);

    MateriRemedial::create([
        'siswa_id' => $siswaGuru->id,
        'topik_lemah' => 'Remedial kelas sendiri',
    ]);
    MateriRemedial::create([
        'siswa_id' => $siswaGuruLain->id,
        'topik_lemah' => 'Remedial kelas lain',
    ]);
    PrediksiAi::create([
        'siswa_id' => $siswaGuru->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 80,
    ]);
    PrediksiAi::create([
        'siswa_id' => $siswaGuruLain->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 90,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.dashboard'))
        ->assertOk()
        ->assertSee('Remedial kelas sendiri')
        ->assertDontSee('Remedial kelas lain')
        ->assertSee($siswaGuru->name)
        ->assertDontSee($siswaGuruLain->name);
});

test('guru kelas index returns 200', function () {
    Kelas::factory()->count(2)->create(['guru_id' => $this->guru->id]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.kelas.index'))
        ->assertOk();
});

test('guru can ajukan available kelas dengan bahan rencana ajar', function () {
    $kelas = Kelas::factory()->create(['guru_id' => null]);

    $this->actingAs($this->guru)
        ->post(route('pengajar.kelas.ajukan.store', $kelas), [
            'rencana_ajar' => 'Mengajarkan aljabar dasar dengan pendekatan kontekstual, urutan dari bilangan bulat sampai persamaan linear.',
        ])
        ->assertRedirect(route('pengajar.kelas.index'));

    $this->assertDatabaseHas('pengajuan_pengajar_kelas', [
        'kelas_id' => $kelas->id,
        'user_id' => $this->guru->id,
        'status' => 'pending',
    ]);

    // Kelas tidak otomatis menjadi milik pengajar — menunggu operator.
    $this->assertDatabaseHas('kelas', ['id' => $kelas->id, 'guru_id' => null]);
});

test('guru cannot ajukan already taken kelas', function () {
    $kelas = Kelas::factory()->create(['guru_id' => User::factory()->pengajar()->verified()->create()->id]);

    $this->actingAs($this->guru)
        ->post(route('pengajar.kelas.ajukan.store', $kelas), [
            'rencana_ajar' => 'Rencana mengajar aljabar dasar dengan urutan materi yang runtut dan latihan bertahap.',
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('pengajuan_pengajar_kelas', 0);
});

test('guru can open materi create page from pertemuan', function () {
    $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.materi.create', ['pertemuan_id' => $pertemuan->id]))
        ->assertOk();
});

test('guru tugas index returns 200', function () {
    $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    Tugas::factory()->count(2)->create([
        'guru_id' => $this->guru->id,
        'kelas_id' => $kelas->id,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.index'))
        ->assertOk();
});

test('guru kuis index returns 200', function () {
    $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.kuis.index'))
        ->assertOk();
});

test('guru prediksi page returns 200', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi'))
        ->assertOk();
});

test('guru rekap nilai page returns 200', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai'))
        ->assertOk();
});

test('guru fitur-siswa page returns 200', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.fitur-siswa'))
        ->assertOk();
});

test('non-guru cannot access guru pages', function () {
    $siswa = User::factory()->verified()->create();

    $this->actingAs($siswa)
        ->get(route('pengajar.dashboard'))
        ->assertRedirect();
});
