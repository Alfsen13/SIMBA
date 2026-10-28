<?php

declare(strict_types=1);

use App\Services\Tugas\SoalTugasService;
use App\Services\Tugas\TugasPresenter;

function tugasSoal(mixed $soal, string $tipe): array
{
    return app(SoalTugasService::class)->normalisasiDaftar($soal, $tipe);
}

function tugasOpsi(array $soal, int $index = 0): array
{
    $baris = tugasSoal([$soal], 'pilihan_ganda');

    return $baris[$index]['pilihan'];
}

it('membaca format pilihan baru', function () {
    $opsi = tugasOpsi([
        'pertanyaan' => 'Berapa 2+2?',
        'pilihan' => ['3', '4', '5', '6'],
        'jawaban' => 1,
    ]);

    expect($opsi)->toBe(['3', '4', '5', '6']);
});

it('membaca format opsi lama sebagai fallback', function () {
    $opsi = tugasOpsi([
        'pertanyaan' => 'Ibu kota Indonesia?',
        'opsi' => ['Jakarta', 'Bandung', 'Surabaya', 'Medan'],
        'jawaban' => 0,
    ]);

    expect($opsi)->toBe(['Jakarta', 'Bandung', 'Surabaya', 'Medan']);
});

it('membuang opsi kelima dan seterusnya', function () {
    $opsi = tugasOpsi([
        'pilihan' => ['a', 'b', 'c', 'd', 'e', 'f'],
        'jawaban' => 0,
    ]);

    expect($opsi)->toHaveCount(4);
    expect($opsi)->toBe(['a', 'b', 'c', 'd']);
});

it('mengisi pilihan kosong sampai empat', function () {
    $opsi = tugasOpsi([
        'pilihan' => ['a', 'b'],
        'jawaban' => 0,
    ]);

    expect($opsi)->toBe(['a', 'b', '', '']);
});

it('membuang opsi bukan string agar tidak jadi array di Blade', function () {
    $opsi = tugasOpsi([
        'pilihan' => ['a', ['nested'], null, 4],
        'jawaban' => 0,
    ]);

    expect($opsi)->toBe(['a', '', '', '4']);
});

it('membuang opsi dari format lama yang bukan array', function () {
    $opsi = tugasOpsi([
        'opsi' => 'bukan array',
        'pilihan' => ['a', 'b', 'c', 'd'],
        'jawaban' => 0,
    ]);

    expect($opsi)->toBe(['a', 'b', 'c', 'd']);
});

it('membuang pilihan yang tidak ada dari kedua format', function () {
    $opsi = tugasOpsi(['pertanyaan' => 'Tanpa pilihan', 'jawaban' => 2]);

    expect($opsi)->toBe(['', '', '', '']);
});

it('menyetakkan kunci jawaban dari string numerik', function () {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => '2']], 'pilihan_ganda');

    expect($soal[0]['jawaban'])->toBe(2);
});

it('menyetakkan kunci jawaban dari float', function () {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 3.0]], 'pilihan_ganda');

    expect($soal[0]['jawaban'])->toBe(3);
});

it('menjepit kunci jawaban di luar rentang ke nol', function (int $jawaban) {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => $jawaban]], 'pilihan_ganda');

    expect($soal[0]['jawaban'])->toBe(0);
})->with([-1, 4, 99]);

it('menjepit kunci jawaban yang bukan angka ke nol', function (mixed $jawaban) {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => $jawaban]], 'pilihan_ganda');

    expect($soal[0]['jawaban'])->toBe(0);
})->with([null, 'abc', '3abc', '1.5', true]);

it('menjepit kunci jawaban array ke nol tanpa melempar error', function () {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => [0]]], 'pilihan_ganda');

    expect($soal[0]['jawaban'])->toBe(0);
});

it('mempertahankan bloom yang valid', function (string $bloom) {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0, 'bloom' => $bloom]], 'pilihan_ganda');

    expect($soal[0]['bloom'])->toBe($bloom);
})->with(['C1', 'C2', 'C3', 'C4', 'C5', 'C6']);

it('membuang bloom di luar daftar level', function (mixed $bloom) {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0, 'bloom' => $bloom]], 'pilihan_ganda');

    expect($soal[0]['bloom'])->toBeNull();
})->with(['C0', 'C7', 'mengingat', '', null, 3]);

it('menormalkan huruf dan spasi bloom dari ai ke bentuk kanonik', function (string $masuk, string $harapan) {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0, 'bloom' => $masuk]], 'pilihan_ganda');

    expect($soal[0]['bloom'])->toBe($harapan);
})->with([
    ['c1', 'C1'],
    [' C1 ', 'C1'],
    ['c3', 'C3'],
    // Tanda kutip ganda supaya \t dan \n benar-benar jadi whitespace.
    ["\tc6\n", 'C6'],
    ["c2\r\n", 'C2'],
]);

it('menyimpan pertanyaan kosong apa adanya dan menyerahkan fallback ke presenter', function () {
    $soal = tugasSoal([['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0]], 'pilihan_ganda');

    // Normalisasi tidak boleh mengarang isi. Mengisi "Soal" di sini akan
    // ikut tersimpan ke DB dan guru tidak bisa lagi tahu baris itu memang
    // belum diisi. Fallback tampilan memang urusan presenter.
    expect($soal[0]['pertanyaan'])->toBe('');

    $tampil = app(TugasPresenter::class)
        ->soalUntukTampil($soal, 'pilihan_ganda');

    expect($tampil[0]['pertanyaan'])->toBe('Soal');
});

it('menghapus kunci opsi agar tidak dobel saat disimpan', function () {
    $soal = tugasSoal([[
        'pertanyaan' => 'P',
        'pilihan' => ['a', 'b', 'c', 'd'],
        'opsi' => ['x', 'y', 'z', 'w'],
        'jawaban' => 1,
    ]], 'pilihan_ganda');

    expect($soal[0])->not->toHaveKey('opsi');
    expect($soal[0]['pilihan'])->toBe(['a', 'b', 'c', 'd']);
});

it('menormalisasi soal esai tanpa pilihan', function () {
    $soal = tugasSoal([
        ['pertanyaan' => 'Jelaskan', 'referensi' => 'Jawaban benar', 'bloom' => 'C4'],
        ['pertanyaan' => 'Urai', 'referensi' => null],
    ], 'essai');

    expect($soal[0]['pertanyaan'])->toBe('Jelaskan');
    expect($soal[0]['referensi'])->toBe('Jawaban benar');
    expect($soal[0]['bloom'])->toBe('C4');
    expect($soal[1]['referensi'])->toBe('');
    expect($soal[1]['bloom'])->toBeNull();
});

it('tidak menghasilkan baris untuk tipe selain pg dan esai', function () {
    expect(tugasSoal([['pertanyaan' => 'Upload']], 'upload'))->toBe([]);
});

it('mengembalikan array kosong untuk input bukan array', function () {
    expect(tugasSoal(null, 'pilihan_ganda'))->toBe([]);
    expect(tugasSoal('bukan array', 'essai'))->toBe([]);
});

it('melewatkan baris yang bukan array tanpa menggagalkan seluruh daftar', function () {
    $soal = tugasSoal([
        'bukan array',
        ['pertanyaan' => 'Sah', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
    ], 'pilihan_ganda');

    expect($soal)->toHaveCount(1);
    expect($soal[0]['pertanyaan'])->toBe('Sah');
});

it('menyamakan nomor indeks setelah baris tidak array dilewati', function () {
    $soal = tugasSoal([
        ['pertanyaan' => 'Satu', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
        'buang',
        ['pertanyaan' => 'Dua', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
    ], 'pilihan_ganda');

    expect(array_keys($soal))->toBe([0, 1]);
    expect($soal[1]['pertanyaan'])->toBe('Dua');
});

it('membaca soal dari string json hasil decode controller', function () {
    $json = json_encode([
        ['pertanyaan' => 'Dari JSON', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 2],
    ]);

    $soal = app(SoalTugasService::class)->decode($json);

    expect($soal[0]['pertanyaan'])->toBe('Dari JSON');
    expect($soal[0]['jawaban'])->toBe(2);
});

it('mengembalikan null dari json rusak atau kosong, bukan array kosong', function (mixed $masuk) {
    // Sengaja null untuk semua kasus: `tugas.soal` nullable, jadi "tidak ada
    // soal" harus null. Kalau jadi [], tipe `upload` dan tipe PG gagal
    // normalisasi jadi tidak bisa dibedakan di DB.
    expect(app(SoalTugasService::class)->decode($masuk))->toBeNull();
})->with([
    '{bukan json',
    '',
    '   ',
    'null',
    '[]',
    '"scalar"',
    '123',
]);

it('mengembalikan null dari decode tanpa argumen', function () {
    expect(app(SoalTugasService::class)->decode(null))->toBeNull();
});

it('menyandikan soal kembali ke json', function () {
    $json = app(SoalTugasService::class)->encode([
        ['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 1],
    ]);

    expect($json)->toBeString();
    expect(json_decode($json, true)[0]['jawaban'])->toBe(1);
});

it('mengembalikan null dari encode saat daftar kosong', function () {
    expect(app(SoalTugasService::class)->encode([]))->toBeNull();
    expect(app(SoalTugasService::class)->encode(null))->toBeNull();
});

it('mencari hasil ai berdasarkan indeks', function () {
    $hasil = [
        ['indeks' => 0, 'status' => 'benar', 'skor' => 90],
        ['indeks' => 1, 'status' => 'salah', 'skor' => 20],
    ];

    expect(app(SoalTugasService::class)->hasilAiUntukIndeks($hasil, 1)['status'])->toBe('salah');
    expect(app(SoalTugasService::class)->hasilAiUntukIndeks($hasil, 9))->toBeNull();
    expect(app(SoalTugasService::class)->hasilAiUntukIndeks(null, 0))->toBeNull();
});
