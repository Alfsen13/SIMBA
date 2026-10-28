<?php

declare(strict_types=1);

use App\Models\PengumpulanTugas;
use App\Services\Tugas\TugasPresenter;

it('menampilkan pilihan format baru dengan kunci jawaban', function () {
    $presenter = app(TugasPresenter::class);

    $baris = $presenter->soalUntukTampil([
        ['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 2],
    ], 'pilihan_ganda');

    expect($baris[0]['opsi'])->toBe(['a', 'b', 'c', 'd'])
        ->and($baris[0]['kunci'])->toBe(2)
        ->and($baris[0]['nomor'])->toBe(1);
});

it('membaca format opsi lama untuk ditampilkan dan menormalkan kunci', function () {
    $presenter = app(TugasPresenter::class);

    $baris = $presenter->soalUntukTampil([
        ['pertanyaan' => 'P', 'opsi' => ['x', 'y', 'z', 'w'], 'jawaban' => '3'],
    ], 'pilihan_ganda');

    expect($baris[0]['opsi'])->toBe(['x', 'y', 'z', 'w'])
        ->and($baris[0]['kunci'])->toBe(3);
});

it('memberikan nomor berurutan 1..n untuk daftar', function () {
    $presenter = app(TugasPresenter::class);

    $baris = $presenter->soalUntukTampil([
        ['pertanyaan' => 'A', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
        ['pertanyaan' => 'B', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 1],
    ], 'pilihan_ganda');

    expect(collect($baris)->pluck('nomor')->all())->toBe([1, 2]);
});

it('memberikan fallback teks soal untuk nilai aneh', function () {
    $presenter = app(TugasPresenter::class);

    $baris = $presenter->soalUntukTampil([
        ['pertanyaan' => '', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
        ['pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
        ['pertanyaan' => ['array'], 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
    ], 'pilihan_ganda');

    foreach ($baris as $b) {
        expect($b['pertanyaan'])->toBe('Soal');
    }
});

it('mengembalikan daftar kosong untuk soal bukan array', function () {
    $presenter = app(TugasPresenter::class);

    expect($presenter->soalUntukTampil(null, 'pilihan_ganda'))->toBe([]);
    expect($presenter->soalUntukTampil('bukan', 'essai'))->toBe([]);
});

it('tidak menghitung baris penilaian untuk tipe selain pg', function () {
    $presenter = app(TugasPresenter::class);

    expect($presenter->soalUntukTampil([['pertanyaan' => 'X']], 'essai'))->toBe([]);
});

it('menyusun baris penilaian dari soal, jawaban, dan hasil ai', function () {
    $pengumpulan = new PengumpulanTugas(['jawaban' => [0 => 'Jawaban 1'], 'hasil_ai' => [
        ['indeks' => 0, 'status' => 'benar', 'skor' => 90, 'alasan' => 'Tepat'],
    ]]);

    $baris = app(TugasPresenter::class)->barisPenilaian([
        ['pertanyaan' => 'Soal 1', 'bloom' => 'C4', 'referensi' => 'Kunci'],
    ], $pengumpulan);

    expect($baris[0]['nomor'])->toBe(1)
        ->and($baris[0]['jawaban'])->toBe('Jawaban 1')
        ->and($baris[0]['hasil']['status'])->toBe('benar')
        ->and($baris[0]['varian'])->toBe('verified')
        ->and($baris[0]['label'])->toBe('Benar')
        ->and($baris[0]['skor'])->toBe(90)
        ->and($baris[0]['alasan'])->toBe('Tepat')
        ->and($baris[0]['bloom'])->toBe('C4')
        ->and($baris[0]['referensi'])->toBe('Kunci');
});

it('memberikan varian warning untuk status yang belum diperiksa', function () {
    $pengumpulan = new PengumpulanTugas(['jawaban' => [0 => 'X']]);

    $baris = app(TugasPresenter::class)->barisPenilaian([
        ['pertanyaan' => 'Soal 1'],
    ], $pengumpulan);

    expect($baris[0]['hasil'])->toBeNull()
        ->and($baris[0]['varian'])->toBe('warning')
        ->and($baris[0]['label'])->toBe('Menunggu');
});

it('memetakan varian untuk tiap status ai', function () {
    $presenter = app(TugasPresenter::class);

    expect($presenter->varianHasil('benar'))->toBe('verified');
    expect($presenter->varianHasil('mendekati'))->toBe('warning');
    expect($presenter->varianHasil('salah'))->toBe('danger');
    expect($presenter->varianHasil('random'))->toBe('warning');
    expect($presenter->varianHasil(null))->toBe('warning');

    expect($presenter->labelHasil('benar'))->toBe('Benar');
    expect($presenter->labelHasil('mendekati'))->toBe('Mendekati');
    expect($presenter->labelHasil('salah'))->toBe('Salah');
    expect($presenter->labelHasil(null))->toBe('Menunggu');
});

it('menyusun pesan periksa ulang ai sesuai tipe dan jumlah', function () {
    $presenter = app(TugasPresenter::class);

    expect($presenter->pesanPeriksaAI('upload', 5))->toBe(['error', 'Fitur pemeriksaan AI hanya tersedia untuk tipe tugas Esai.']);
    expect($presenter->pesanPeriksaAI('essai', 0))->toBe(['error', 'Tidak ada pengumpulan yang perlu diperiksa ulang.']);
    expect($presenter->pesanPeriksaAI('essai', 3))->toBe(['success', 'Pemeriksaan AI dijalankan di antrean. Segarkan halaman ini sebentar untuk melihat hasil terbaru.']);
});

it('menampilkan pesan submit sesuai tipe dan keterlambatan', function () {
    $presenter = app(TugasPresenter::class);

    expect($presenter->pesanSubmit('upload', false))->toBe('Tugas berhasil dikumpulkan.');
    expect($presenter->pesanSubmit('pilihan_ganda', true))->toBe('Tugas berhasil dikumpulkan (terlambat).');
    expect($presenter->pesanSubmit('essai', false))->toBe('Tugas berhasil dikumpulkan. Jawabanmu sedang diperiksa oleh AI.');
});

it('menyusun soal siswa untuk essai memakai baris penilaian', function () {
    $pengumpulan = new PengumpulanTugas(['jawaban' => [0 => 'Jawaban'], 'hasil_ai' => null]);

    $baris = app(TugasPresenter::class)->soalUntukSiswa([
        ['pertanyaan' => 'Jelaskan', 'referensi' => 'Kunci'],
    ], 'essai', $pengumpulan);

    expect($baris[0]['pertanyaan'])->toBe('Jelaskan')
        ->and($baris[0]['jawaban'])->toBe('Jawaban')
        ->and($baris[0]['hasil'])->toBeNull()
        ->and($baris[0]['label'])->toBe('Menunggu');
});

it('menyusun soal siswa untuk pilihan ganda memakai bentuk tampil', function () {
    $baris = app(TugasPresenter::class)->soalUntukSiswa([
        ['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 1],
    ], 'pilihan_ganda');

    expect($baris[0]['opsi'])->toBe(['a', 'b', 'c', 'd'])
        ->and($baris[0]['kunci'])->toBe(1);
});

it('menyusun baris pilihan ganda dengan jawaban siswa dan kunci', function () {
    $pengumpulan = new PengumpulanTugas(['jawaban' => [0 => '2', 1 => '0']]);

    $baris = app(TugasPresenter::class)->barisPilihanGanda([
        ['pertanyaan' => 'P1', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 2],
        ['pertanyaan' => 'P2', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 1],
    ], $pengumpulan);

    expect($baris[0]['nomor'])->toBe(1)
        ->and($baris[0]['jawaban'])->toBe(2)
        ->and($baris[0]['jawaban_teks'])->toBe('c')
        ->and($baris[0]['kunci'])->toBe(2)
        ->and($baris[0]['kunci_teks'])->toBe('c')
        ->and($baris[0]['benar'])->toBeTrue()
        ->and($baris[1]['jawaban'])->toBe(0)
        ->and($baris[1]['jawaban_teks'])->toBe('a')
        ->and($baris[1]['kunci_teks'])->toBe('b')
        ->and($baris[1]['benar'])->toBeFalse();
});

it('menandai pilihan ganda yang belum dijawab', function () {
    $baris = app(TugasPresenter::class)->barisPilihanGanda([
        ['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
    ], new PengumpulanTugas(['jawaban' => []]));

    expect($baris[0]['jawaban'])->toBeNull()
        ->and($baris[0]['jawaban_teks'])->toBeNull()
        ->and($baris[0]['benar'])->toBeFalse();
});
