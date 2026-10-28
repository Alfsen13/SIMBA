<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;

beforeEach(function () {
    $this->siswa = User::factory()->verified()->create();
});

test('siswa dashboard returns 200', function () {
    $this->actingAs($this->siswa)
        ->get(route('pelajar.dashboard'))
        ->assertOk();
});

test('siswa kelas index returns 200', function () {
    $this->actingAs($this->siswa)
        ->get(route('pelajar.kelas.index'))
        ->assertOk();
});

test('siswa tugas index returns 200', function () {
    $this->actingAs($this->siswa)
        ->get(route('pelajar.tugas.index'))
        ->assertOk();
});

test('siswa kuis index returns 200', function () {
    $this->actingAs($this->siswa)
        ->get(route('pelajar.kuis.index'))
        ->assertOk();
});

test('siswa nilai page returns 200', function () {
    $this->actingAs($this->siswa)
        ->get(route('pelajar.nilai'))
        ->assertOk();
});

test('siswa ai-tutor index shows kelas with materis', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    Materi::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.ai-tutor'))
        ->assertOk()
        ->assertSee($kelas->nama_kelas);
});

test('siswa ai-tutor pertemuan page returns 200', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    $materi = Materi::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.ai-tutor.pertemuan', $kelas))
        ->assertOk()
        ->assertSee($materi->judul);
});

test('siswa ai-tutor chat page returns 200', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    $materi = Materi::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.ai-tutor.chat', [$kelas, $pertemuan]))
        ->assertOk()
        ->assertSee($materi->judul);
});

test('siswa can view kelas detail', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kelas.show', $kelas))
        ->assertOk();
});

test('siswa can view materi', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $materi = Materi::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
    ]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.materi.show', $materi))
        ->assertOk();
});

test('siswa can see available kelas list', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas1 = Kelas::factory()->create(['guru_id' => $guru->id]);
    Kelas::factory()->create(['guru_id' => $guru->id]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee($kelas1->nama_kelas);
});

test('siswa can request to enroll in available kelas', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.enroll', $kelas))
        ->assertRedirect(route('pelajar.kelas.index'));

    // Belum jadi anggota aktif — masih menunggu validasi operator.
    expect($this->siswa->kelasDiikuti->pluck('id'))->not->toContain($kelas->id)
        ->and($this->siswa->kelasMenunggu->pluck('id'))->toContain($kelas->id);
});

test('siswa cannot enroll twice', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.enroll', $kelas))
        ->assertStatus(409);
});

test('siswa cannot request enroll twice while pending', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $this->siswa->semuaKelasDiikuti()->attach($kelas->id, ['status' => 'pending']);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.enroll', $kelas))
        ->assertStatus(409);
});

test('siswa can unenroll from enrolled kelas', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $this->siswa->kelasDiikuti()->attach($kelas->id);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.unenroll', $kelas))
        ->assertRedirect(route('pelajar.kelas.index'));

    expect($this->siswa->kelasDiikuti->pluck('id'))->not->toContain($kelas->id);
});

test('siswa cannot unenroll from non-enrolled kelas', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.unenroll', $kelas))
        ->assertForbidden();
});

test('non-siswa cannot access siswa pages', function () {
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)
        ->get(route('pelajar.dashboard'))
        ->assertRedirect(route('operator.dashboard'));
});
