<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Materi;
use App\Models\MateriRemedial;
use App\Models\NilaiHarian;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;

beforeEach(function () {
    $this->siswa = User::factory()->verified()->create();
});

test('siswa dashboard returns 200', function () {
    $this->actingAs($this->siswa)
        ->get(route('pelajar.dashboard'))
        ->assertOk();
});

test('siswa dashboard labels unread remedial status', function () {
    MateriRemedial::create([
        'siswa_id' => $this->siswa->id,
        'topik_lemah' => 'Materi uji',
        'status' => 'belum_dibaca',
    ]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.dashboard'))
        ->assertOk()
        ->assertSee(__('siswa.shared.status_belum_dibaca'));
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

test('pagination tiap kelompok nilai dapat digunakan tanpa mengubah halaman kelompok lain', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    for ($i = 1; $i <= 21; $i++) {
        NilaiHarian::create(['siswa_id' => $this->siswa->id, 'kelas_id' => $kelas->id, 'nilai' => $i]);
        $kuis = Kuis::create(['kelas_id' => $kelas->id, 'judul' => "Kuis nilai $i", 'soal' => []]);
        KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $this->siswa->id, 'skor' => $i]);
        $tugas = Tugas::factory()->create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'judul' => "Tugas nilai $i"]);
        PengumpulanTugas::create(['tugas_id' => $tugas->id, 'siswa_id' => $this->siswa->id, 'nilai' => $i]);
    }

    $this->actingAs($this->siswa)
        ->get(route('pelajar.nilai', ['harian_page' => 2, 'kuis_page' => 1, 'tugas_page' => 2]))
        ->assertOk()
        ->assertViewHas('nilaiHarian', fn ($p) => $p->currentPage() === 2 && $p->first()->nilai === 1)
        ->assertViewHas('nilaiTugas', fn ($p) => $p->currentPage() === 2 && $p->first()->nilai === 1)
        ->assertViewHas('nilaiKuis', fn ($p) => $p->currentPage() === 1 && $p->count() === 20)
        ->assertSee('Kuis nilai 21')
        ->assertDontSee('Kuis nilai 1<', false)
        ->assertSee('kuis_page=2', false)
        ->assertSee('harian_page=2', false)
        ->assertSee('tugas_page=2', false);
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
