<?php

use App\Models\Kelas;
use App\Models\MateriRemedial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Prefix helper unik per file (fungsi global di Pest) — jangan diganti generik.
function rlSiswa(): User
{
    return User::factory()->verified()->create();
}

function rlGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function rlKelas(User $guru, User $siswa): Kelas
{
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $kelas;
}

function rlRemedial(User $siswa, string $latihan): MateriRemedial
{
    return MateriRemedial::create([
        'siswa_id' => $siswa->id,
        'topik_lemah' => 'Topik uji',
        'ringkasan' => 'Ringkasan materi uji untuk remedial.',
        'latihan' => $latihan,
        'provider_llm' => 'ollama',
        'status' => 'belum_dibaca',
        'generated_at' => now(),
    ]);
}

it('mengurai latihan JSON per baris menjadi daftar tanya-jawab', function () {
    $siswa = rlSiswa();

    $latihan = implode("\n", [
        json_encode(['soal' => 'Soal A?', 'jawaban' => 'Jawaban A.'], JSON_UNESCAPED_UNICODE),
        json_encode(['soal' => 'Soal B?', 'jawaban' => 'Jawaban B.'], JSON_UNESCAPED_UNICODE),
    ]);

    $items = rlRemedial($siswa, $latihan)->latihan_items;

    expect($items)->toHaveCount(2)
        ->and($items[0])->toMatchArray(['soal' => 'Soal A?', 'jawaban' => 'Jawaban A.'])
        ->and($items[1])->toMatchArray(['soal' => 'Soal B?', 'jawaban' => 'Jawaban B.']);
});

it('mengurai latihan berupa array JSON tunggal', function () {
    $siswa = rlSiswa();

    $latihan = json_encode([
        ['soal' => 'X?', 'jawaban' => 'Y.'],
        ['soal' => 'Z?', 'jawaban' => 'W.'],
    ], JSON_UNESCAPED_UNICODE);

    $items = rlRemedial($siswa, $latihan)->latihan_items;

    expect($items)->toHaveCount(2)
        ->and($items[0]['soal'])->toBe('X?')
        ->and($items[1]['jawaban'])->toBe('W.');
});

it('membuang latihan yang persis duplikat', function () {
    $siswa = rlSiswa();

    $latihan = implode("\n", [
        json_encode(['soal' => 'Sama?', 'jawaban' => 'Sama.'], JSON_UNESCAPED_UNICODE),
        json_encode(['soal' => 'Sama?', 'jawaban' => 'Sama.'], JSON_UNESCAPED_UNICODE),
        json_encode(['soal' => 'Beda?', 'jawaban' => 'Beda.'], JSON_UNESCAPED_UNICODE),
    ]);

    $items = rlRemedial($siswa, $latihan)->latihan_items;

    expect($items)->toHaveCount(2)
        ->and($items[0]['soal'])->toBe('Sama?')
        ->and($items[1]['soal'])->toBe('Beda?');
});

it('mengembalikan daftar kosong bila latihan bukan JSON', function () {
    $siswa = rlSiswa();

    $items = rlRemedial($siswa, "1. Apa itu?\nJawaban: begini.")->latihan_items;

    expect($items)->toBe([]);
});

it('menampilkan latihan mentah sebagai tanya-jawab di halaman siswa', function () {
    $guru = rlGuru();
    $siswa = rlSiswa();
    rlKelas($guru, $siswa);

    $remedial = rlRemedial($siswa, json_encode([
        'soal' => 'Apa itu manajemen inventaris?',
        'jawaban' => 'Manajemen inventaris adalah pengelolaan barang.',
    ], JSON_UNESCAPED_UNICODE));

    $this->actingAs($siswa)
        ->get(route('pelajar.remedial.show', $remedial))
        ->assertOk()
        ->assertSee('Apa itu manajemen inventaris?')
        ->assertSee('Manajemen inventaris adalah pengelolaan barang.')
        ->assertSee('Lihat Jawaban')
        // Jawaban dibungkus toggle Alpine dengan kondisi awal tersembunyi.
        ->assertSee('x-data="{ buka: false }"', false)
        ->assertDontSee('&quot;soal&quot;', false);
});

it('menampilkan latihan mentah sebagai tanya-jawab di halaman guru', function () {
    $guru = rlGuru();
    $siswa = rlSiswa();
    rlKelas($guru, $siswa);

    $remedial = rlRemedial($siswa, json_encode([
        'soal' => 'Bagaimana manajemen stok?',
        'jawaban' => 'Dengan mengelola stok barang.',
    ], JSON_UNESCAPED_UNICODE));

    $this->actingAs($guru)
        ->get(route('pengajar.remedial.show', $remedial))
        ->assertOk()
        ->assertSee('Bagaimana manajemen stok?')
        ->assertSee('Dengan mengelola stok barang.')
        ->assertSee('Lihat Jawaban')
        ->assertSee('x-data="{ buka: false }"', false)
        ->assertDontSee('&quot;soal&quot;', false);
});
