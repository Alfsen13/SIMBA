<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
 * Halaman form sebelumnya memakai `max-w-xl` / `max-w-2xl` pada card-nya.
 * Batas itu ditempel di KIRI tanpa `mx-auto`, jadi di monitor lebar card cuma
 * occupy 576-672px dan separuh layar jadi ruang kosong. Gejalanya dilaporkan
 * user sebagai "card terpotong di kanan, ada penghalang".
 *
 * Test ini menjaga card form mengikuti lebar layar. Kalau suatu saat ada yang
 * menambahkan `max-w-*` lagi, halaman akan kembali setengah dan test ini gagal.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.kuis_rag.enabled' => false,
        'services.symbols.enabled' => false,
    ]);

    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    Materi::create([
        'judul' => 'Materi Satu',
        'bab' => 'Bab 1',
        'deskripsi' => 'Deskripsi',
        'pertemuan_id' => $this->pertemuan->id,
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
    ]);
});

test('halaman form pertemuan tidak lagi dibatasi max-w', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.kelas.pertemuan.create', $this->kelas))
        ->assertOk()
        ->assertDontSee('max-w-xl', escape: false)
        ->assertDontSee('max-w-2xl', escape: false);
});

test('halaman form pertemuan membagi isian ke dua kolom responsif', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.kelas.pertemuan.create', $this->kelas))
        ->assertOk()
        ->assertSee('flex flex-wrap -mx-3', escape: false)
        ->assertSee('md:basis-1/2', escape: false);
});

test('halaman edit kelas tidak lagi dibatasi max-w dan formnya utuh', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.kelas.edit', $this->kelas))
        ->assertOk()
        ->assertDontSee('max-w-xl', escape: false)
        // Tombol submit harus tetap ada: field ini pernah hilang saat refactor layout.
        ->assertSee('type="submit"', escape: false);
});

test('halaman buat tugas tidak lagi dibatasi max-w', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->assertDontSee('max-w-2xl', escape: false);
});

test('halaman buat tugas punya tepat satu input deadline', function () {
    // Field deadline sempat muncul dua kali saat dipindah ke kolom dua:
    // form yang mengirim key sama dua kali itu ambigu once server baca.
    $html = $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->getContent();

    expect(substr_count($html, 'name="deadline"'))->toBe(1);
});

test('halaman buat tugas tetap punya tombol simpan dan input soal', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->assertSee('type="submit"', escape: false)
        ->assertSee('name="soal"', escape: false);
});

test('halaman buat kuis tidak lagi dibatasi max-w', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.kuis.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->assertDontSee('max-w-2xl', escape: false)
        ->assertSee('type="submit"', escape: false);
});

test('halaman buat materi tidak lagi dibatasi max-w', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.materi.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->assertDontSee('max-w-xl', escape: false)
        ->assertSee('type="submit"', escape: false);
});

test('komponen form-columns benar-benar mengeluarkan isian di dalamnya', function () {
    // `x-form-columns` versi pertama tidak mengeluarkan `{{ $slot }}`. Blade
    // tidak error untuk slot kosong, jadi seluruh field lenyap tanpa jejak dan
    // halaman form tampil sebagai kartu kosong. Assertion ini yang menangkapnya.
    $html = $this->actingAs($this->guru)
        ->get(route('pengajar.kelas.pertemuan.create', $this->kelas))
        ->assertOk()
        ->getContent();

    foreach (['nama_pertemuan', 'urutan', 'deskripsi', 'type="submit"'] as $wajib) {
        expect($html)->toContain($wajib);
    }
});
