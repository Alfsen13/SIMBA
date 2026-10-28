<?php

use App\Services\BloomTaxonomyService;

beforeEach(function () {
    $this->bloom = new BloomTaxonomyService;
});

test('dataset bloom memuat enam level C1 sampai C6', function () {
    $levels = $this->bloom->levels();

    expect($levels)->toHaveCount(6)
        ->and(array_column($levels, 'code'))->toBe(['C1', 'C2', 'C3', 'C4', 'C5', 'C6']);
});

test('metadata dataset diringkas dari bloom.json', function () {
    $meta = $this->bloom->metadata();

    expect($meta)->toHaveKeys(['name', 'version', 'language', 'framework', 'levels', 'examples'])
        ->and($meta['version'])->toBe('1.0.0')
        ->and($meta['levels'])->toBe(6)
        ->and($meta['examples'])->toBe(12);
});

test('kode level tervalidasi dan di-normalisasi', function (string $masuk, string $harapan) {
    expect($this->bloom->valid($masuk))->toBe($harapan);
})->with([
    ['C3', 'C3'],
    ['c5', 'C5'],
    ['  c1  ', 'C1'],
    ['C9', 'C2'],
    ['zz', 'C2'],
    ['', 'C2'],
]);

test('label dan shortLabel memakai nama dari dataset', function () {
    // Urutan revisi Anderson & Krathwohl: C3 Menerapkan, C4 Menganalisis.
    expect($this->bloom->label('C3'))->not->toBe('')
        ->and($this->bloom->label('C3'))->toContain('Menerapkan')
        ->and($this->bloom->label('C4'))->toContain('Menganalisis')
        ->and($this->bloom->shortLabel('C6'))->toBe('Mencipta');
});

test('rentang kesulitan mengikuti distribusi bertahap yang dipilih', function () {
    expect($this->bloom->allowedRange('mudah'))->toBe(['C1', 'C2'])
        ->and($this->bloom->allowedRange('sedang'))->toBe(['C2', 'C3', 'C4'])
        ->and($this->bloom->allowedRange('sulit'))->toBe(['C3', 'C4', 'C5'])
        ->and($this->bloom->allowedRange('entah'))->toBe(['C2', 'C3', 'C4']);
});

test('distribusi soal menjumlah tepat ke jumlah yang diminta', function (string $kesulitan, int $jumlah) {
    $distribusi = $this->bloom->distribute($jumlah, $kesulitan);

    expect(array_sum($distribusi))->toBe($jumlah)
        ->and(array_keys($distribusi))->each->toBeIn($this->bloom->allowedRange($kesulitan));
})->with([
    ['mudah', 1],
    ['mudah', 5],
    ['sedang', 4],
    ['sedang', 7],
    ['sulit', 3],
    ['sulit', 12],
]);

test('distribusi tidak pernah mengembalikan level di luar rentang', function () {
    $distribusi = $this->bloom->distribute(5, 'sulit');

    expect($distribusi)->not->toHaveKey('C2')
        ->and($distribusi)->not->toHaveKey('C6');
});

test('clampToRange mengunci level nyasar ke rentang kesulitan', function (string $kesulitan, string $masuk, string $harapan) {
    expect($this->bloom->clampToRange($masuk, $kesulitan))->toBe($harapan);
})->with([
    ['mudah', 'C5', 'C2'],
    ['sedang', 'C1', 'C2'],
    ['sulit', 'C6', 'C5'],
    ['sulit', 'C1', 'C3'],
    ['sedang', 'C3', 'C3'],
]);

test('clamp tanpa target kesulitan tidak mengubah level', function () {
    expect($this->bloom->clampToRange('C1', null))->toBe('C1')
        ->and($this->bloom->clampToRange('C6', null))->toBe('C6')
        ->and($this->bloom->clampToRange('C5', 'entah'))->toBe('C5');
});

test('clampToDefaultRange mengunci level esai ke C2-C4', function (string $masuk, string $harapan) {
    expect($this->bloom->clampToDefaultRange($masuk))->toBe($harapan);
})->with([
    ['C1', 'C2'],
    ['C5', 'C4'],
    ['C6', 'C4'],
    ['C3', 'C3'],
]);

test('clampToSpan dipakai untuk rentang SAP remedial C1-C5', function (string $masuk, string $harapan) {
    expect($this->bloom->clampToSpan($masuk, 'C1', 'C5'))->toBe($harapan);
})->with([
    ['C1', 'C1'],
    ['C2', 'C2'],
    ['C5', 'C5'],
    ['C6', 'C5'],
]);

test('deteksi lokal mengenali seluruh contoh klasifikasi dataset', function () {
    $contoh = $this->bloom->data()['question_classification_examples'] ?? [];
    expect($contoh)->not->toBeEmpty();

    foreach ($contoh as $baris) {
        $pertanyaan = (string) ($baris['question'] ?? '');
        $ekspektasi = (string) ($baris['bloom_code'] ?? '');

        expect($this->bloom->detect($pertanyaan))
            ->toBe($ekspektasi, "Gagal deteksi: {$pertanyaan}");
    }
});

test('detektor lokal mengembalikan null untuk teks tanpa sinyal', function () {
    expect($this->bloom->detect(''))->toBeNull();
});

test('decide mempercayai AI saat heuristik tidak yakin', function () {
    $kode = $this->bloom->decide('Soal tanpa kata kerja kognitif sama sekali', 'C4', 'sedang');

    expect($kode)->toBe('C4');
});

test('decide mengoreksi level AI yang bertentangan dengan kata kerja', function () {
    $kode = $this->bloom->decide('Analisis pengaruh suhu terhadap laju reaksi', 'C1', 'sedang');

    expect($kode)->toBe('C4');
});

test('decide tetap mengunci hasil ke rentang kesulitan guru', function () {
    $kode = $this->bloom->decide('Buat mahakarya orisinal tentang ekosistem', 'C6', 'sedang');

    expect($kode)->toBeIn(['C2', 'C3', 'C4']);
});

test('rubrik dan panduan penyusun soal memakai isi dataset', function () {
    $rubrik = $this->bloom->rubric('generate');

    expect($rubrik)->toContain('C1')
        ->and($rubrik)->toContain('C6')
        ->and($rubrik)->toContain('KLASIFIKASI');
});

test('panduan kesulitan menyebut sebaran sesuai jumlah soal', function () {
    $beberapa = $this->bloom->difficultyGuidance('sulit', 3);
    $banyak = $this->bloom->difficultyGuidance('sulit', 12);

    expect($beberapa)->toContain('Contoh sebaran untuk 3 soal')
        ->and($banyak)->toContain('Contoh sebaran untuk 12 soal')
        ->and($this->bloom->difficultyGuidance('sulit', 3))->toContain('C3-C5');
});

test('contoh klasifikasi dan disambiguasi kata kerja tersedia untuk prompt', function () {
    $contoh = $this->bloom->classificationExamples(3);
    $verba = $this->bloom->verbDisambiguationText();

    expect(substr_count($contoh, "\n"))->toBe(2)
        ->and($verba)->toContain('mengidentifikasi');
});

test('lengkapiOtomatis mengisi level soal yang belum punya level', function () {
    $hasil = $this->bloom->lengkapiOtomatis([
        ['pertanyaan' => 'Apa ibu kota Indonesia?'],
        ['pertanyaan' => 'Jelaskan proses fotosintesis pada tumbuhan.'],
    ]);

    foreach ($hasil as $baris) {
        expect($baris['bloom'])->toBeIn(['C1', 'C2', 'C3', 'C4', 'C5', 'C6']);
    }
});

test('lengkapiOtomatis tidak menimpa level yang sudah dipilih guru', function () {
    $hasil = $this->bloom->lengkapiOtomatis([
        ['pertanyaan' => 'Sebutkan ciri metamorfosis kupu-kupu.', 'bloom' => 'C5'],
        ['pertanyaan' => 'Analisis data suhu percobaan.', 'bloom' => 'C1'],
    ]);

    expect($hasil[0]['bloom'])->toBe('C5')
        ->and($hasil[1]['bloom'])->toBe('C1');
});

test('lengkapiOtomatis mengisi level kosong string dan nilai ngawur', function () {
    $hasil = $this->bloom->lengkapiOtomatis([
        ['pertanyaan' => 'Apa ibu kota Indonesia?', 'bloom' => ''],
        ['pertanyaan' => 'Apa ibu kota Indonesia?', 'bloom' => 'ngawur'],
    ]);

    expect($hasil[0]['bloom'])->toBeIn(['C1', 'C2', 'C3', 'C4', 'C5', 'C6'])
        ->and($hasil[1]['bloom'])->toBeIn(['C1', 'C2', 'C3', 'C4', 'C5', 'C6']);
});

test('lengkapiOtomatis mengunci level ke rentang C2-C4 tanpa tingkat kesulitan', function () {
    // "Rancang" adalah kata kerja C6. Tanpa target kesulitan, soal esai tetap
    // harus berada di rentang default C2-C4, bukan ikut naik ke C6.
    $hasil = $this->bloom->lengkapiOtomatis([
        ['pertanyaan' => 'Rancang percobaan untuk menguji pengaruh suhu pada laju reaksi'],
    ]);

    expect($hasil[0]['bloom'])->toBeIn(['C2', 'C3', 'C4']);
});

test('lengkapiOtomatis menghormati tingkat kesulitan yang dipilih guru', function () {
    $soal = [['pertanyaan' => 'Rancang percobaan untuk menguji pengaruh suhu pada laju reaksi']];

    expect($this->bloom->lengkapiOtomatis($soal, 'mudah')[0]['bloom'])->toBeIn(['C1', 'C2'])
        ->and($this->bloom->lengkapiOtomatis($soal, 'sulit')[0]['bloom'])->toBeIn(['C3', 'C4', 'C5']);
});

test('lengkapiOtomatis menormalkan huruf kecil level yang sudah ada', function () {
    $hasil = $this->bloom->lengkapiOtomatis([['pertanyaan' => 'Apa itu fotosintesis?', 'bloom' => 'c4']]);

    expect($hasil[0]['bloom'])->toBe('C4');
});

test('lengkapiOtomatis dilewati saat flag BLOOM_ENABLED mati', function () {
    config(['services.bloom.enabled' => false]);

    $soal = [['pertanyaan' => 'Apa ibu kota Indonesia?']];

    expect($this->bloom->lengkapiOtomatis($soal))->toBe($soal);
});

test('lengkapiOtomatis tidak melempar saat baris bukan array', function () {
    $hasil = $this->bloom->lengkapiOtomatis([
        ['pertanyaan' => 'Jelaskan fotosintesis.'],
        'bukan array',
        ['pertanyaan' => 'Apa ibu kota Indonesia?'],
    ]);

    // Baris bukan array diteruskan apa adanya: membuang baris adalah urusan
    // normalisasiDaftar, lengkapiOtomatis hanya boleh menambah level.
    expect($hasil)->toHaveCount(3)
        ->and($hasil[1])->toBe('bukan array');
});
