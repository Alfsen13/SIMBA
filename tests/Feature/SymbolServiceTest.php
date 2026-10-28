<?php

use App\Services\AiJsonParser;
use App\Services\SymbolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.symbols.enabled' => true,
        'services.symbols.top_k' => 6,
        'services.symbols.min_score' => 1.0,
        'services.symbols.max_chars' => 2400,
    ]);

    DB::table('symbols')->insert([
        [
            'id' => 'calc.int.parts',
            'name' => 'Integral Parsial',
            'symbol' => null,
            'formula' => 'uv - integral v du',
            'latex' => null,
            'definition' => 'Rumus integrasi per bagian dari hasil kali.',
            'meaning' => 'Mengubah integral produk menjadi selisih dua integral sederhana.',
            'example' => null,
            'common_misunderstanding' => null,
            'unit' => null,
            'category' => 'Integral',
            'subcategory' => null,
            'keywords' => json_encode(['integral', 'parsial', 'per bagian']),
            'related_concepts' => json_encode([]),
            'related_symbols' => json_encode([]),
            'domain' => 'kalkulus',
            'retrieval_text' => 'integral parsial per bagian uv integrasi per bagian',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 'phys.gravity',
            'name' => 'Gravitasi Newton',
            'symbol' => 'F = G m1 m2 / r^2',
            'formula' => 'F = G m1 m2 / r^2',
            'latex' => null,
            'definition' => 'Gaya tarik antara dua benda bermassa.',
            'meaning' => 'Besar gaya sebanding dengan massa dan berlawanan arah dengan kuadrat jarak.',
            'example' => null,
            'common_misunderstanding' => null,
            'unit' => 'newton',
            'category' => 'Mekanika',
            'subcategory' => 'Gravitasi',
            'keywords' => json_encode(['gravitasi', 'newton', 'gaya tarik', 'menarik', 'massa']),
            'related_concepts' => json_encode([]),
            'related_symbols' => json_encode([]),
            'domain' => 'fisika',
            'retrieval_text' => 'gravitasi newton gaya tarik massa F=Gm1m2/r2 newton',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 'chem.ph.value',
            'name' => 'Skala pH',
            'symbol' => 'pH = -log[H+]',
            'formula' => 'pH = -log[H+]',
            'latex' => null,
            'definition' => 'Ukuran derajat keasaman suatu larutan.',
            'meaning' => 'Nilai kecil berarti asam kuat, nilai besar berarti basa.',
            'example' => null,
            'common_misunderstanding' => null,
            'unit' => null,
            'category' => 'Asam Basa',
            'subcategory' => 'pH',
            'keywords' => json_encode(['ph', 'asam', 'basa', 'keasaman']),
            'related_concepts' => json_encode([]),
            'related_symbols' => json_encode([]),
            'domain' => 'kimia',
            'retrieval_text' => 'ph asam basa keasaman derajat asam log konsentrasi ion hidrogen',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 'math.gen.persamaan',
            'name' => 'Persamaan Garis Lurus',
            'symbol' => 'y = mx + b',
            'formula' => 'y = mx + b',
            'latex' => null,
            'definition' => 'Bentuk persamaan garis dengan gradien tetap.',
            'meaning' => 'Gradien adalah kemiringan garis.',
            'example' => null,
            'common_misunderstanding' => null,
            'unit' => null,
            'category' => 'Aljabar',
            'subcategory' => 'Persamaan',
            'keywords' => json_encode(['persamaan', 'garis', 'gradien', 'linear']),
            'related_concepts' => json_encode([]),
            'related_symbols' => json_encode([]),
            'domain' => 'matematika',
            'retrieval_text' => 'persamaan garis lurus gradien linear y=mx+b kemiringan',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

it('mengambil simbol yang paling relevan untuk query kalkulus', function () {
    $hasil = app(SymbolService::class)->retrieve('rumus integral parsial', 'kalkulus', 5);

    expect($hasil)->not->toBeEmpty()
        ->and($hasil->first()->id)->toBe('calc.int.parts');
});

it('menyorot istilah khas di atas istilah umum', function () {
    // "persamaan" ada di math.gen.persamaan, "gravitasi" hanya di phys.gravity.
    $hasil = app(SymbolService::class)->retrieve('persamaan gravitasi newton');

    expect($hasil->first()->id)->toBe('phys.gravity');
});

it('membatasi hasil pada domain yang diminta', function () {
    $hasil = app(SymbolService::class)->retrieve('asam', 'kimia', 5);

    expect($hasil->pluck('domain')->unique()->all())->toBe(['kimia']);
});

it('mengembalikan kosong bila tidak ada domain yang cocok', function () {
    $hasil = app(SymbolService::class)->retrieve('gravitasi', 'biologi', 5);

    expect($hasil)->toBeEmpty();
});

it('mengembalikan kosong bila query hanya berisi kata umum', function () {
    $hasil = app(SymbolService::class)->retrieve('yang dan di', null, 5);

    expect($hasil)->toBeEmpty();
});

it('tidak retrieves saat dimatikan lewat config', function () {
    config(['services.symbols.enabled' => false]);

    expect(app(SymbolService::class)->retrieve('gravitasi'))->toBeEmpty();
});

it('menghormati batas limit', function () {
    $hasil = app(SymbolService::class)->retrieve('persamaan', null, 1);

    expect($hasil)->toHaveCount(1);
});

it('memetakan kategori kelas ke domain', function () {
    $svc = app(SymbolService::class);

    expect($svc->resolveDomains('Matematika'))->toBe(['matematika', 'kalkulus'])
        ->and($svc->resolveDomains('Fisika'))->toBe(['fisika'])
        ->and($svc->resolveDomains('Kimia'))->toBe(['kimia'])
        ->and($svc->resolveDomains('Biologi'))->toBe(['biologi']);
});

it('mengembalikan null untuk kategori yang tidak jelas agar tidak nol hasil', function () {
    $svc = app(SymbolService::class);

    expect($svc->resolveDomains('IPA'))->toBeNull()
        ->and($svc->resolveDomains('Bahasa Inggris'))->toBeNull()
        ->and($svc->resolveDomains(null))->toBeNull()
        ->and($svc->resolveDomains('  '))->toBeNull();
});

it('menyusun blok konteks dalam batas karakter', function () {
    $svc = app(SymbolService::class);

    $konteks = $svc->buildContext('gravitasi newton', ['fisika'], 3, 400);

    expect($konteks)->toContain('Gravitasi Newton')
        ->and($konteks)->toContain('newton')
        ->and(mb_strlen($konteks))->toBeLessThan(500);
});

it('mengembalikan string kosong bila tidak ada simbol relevan', function () {
    $konteks = app(SymbolService::class)->buildContext('zzzzz tidak ada', ['biologi']);

    expect($konteks)->toBe('');
});

it('memperbaiki kunci objek yang lupa tanda kutip penutup', function () {
    // Kasus nyata dari simba-tutor-slim: model menulis "jawaban:0} alih-alih
    // "jawaban":0}, sehingga json_decode gagal.
    $rusak = <<<'JSON'
    [
        {"pertanyaan":"Mana yang salah?","pilihan":["a","b","c","d"],"jawaban:0},
        {"pertanyaan":"Mana yang benar?","pilihan":["e","f","g","h"],"jawaban":2}
    ]
    JSON;

    $hasil = AiJsonParser::arrayFrom($rusak);

    expect($hasil)->toHaveCount(2)
        ->and($hasil[0]['jawaban'])->toBe(0)
        ->and($hasil[0]['pilihan'])->toBe(['a', 'b', 'c', 'd'])
        ->and($hasil[1]['jawaban'])->toBe(2);
});

it('tidak merusak nilai string yang memuat titik dua', function () {
    $teks = '[{"pertanyaan":"Jelaskan cara: saat ini jam 9","pilihan":["waktu: 5 menit","x","y","z"],"jawaban":0}]';

    $hasil = AiJsonParser::arrayFrom($teks);

    expect($hasil)->toHaveCount(1)
        ->and($hasil[0]['pertanyaan'])->toBe('Jelaskan cara: saat ini jam 9')
        ->and($hasil[0]['pilihan'][0])->toBe('waktu: 5 menit')
        ->and($hasil[0]['jawaban'])->toBe(0);
});

it('hanya memakai satu domain ketika diberikan daftar', function () {
    $svc = app(SymbolService::class);

    $hasil = $svc->retrieve('persamaan', ['kimia'], 5);

    expect($hasil)->toBeEmpty();
});

it('mengurutkan hasil secara stabil untuk skor yang sama', function () {
    $svc = app(SymbolService::class);

    $pertama = $svc->retrieve('persamaan', null, 5)->pluck('id')->all();
    $kedua = app(SymbolService::class)->retrieve('persamaan', null, 5)->pluck('id')->all();

    expect($pertama)->toBe($kedua);
});

it('memberi anotasi simbol pada garis FORMULA hasil ekstraksi PDF', function () {
    DB::table('symbols')->insert([
        'id' => 'calc.int.definite',
        'name' => 'Integral Tertentu',
        'symbol' => null,
        'formula' => null,
        'latex' => '\int_{a}^{b} f(x)\,dx = F(b) - F(a)',
        'definition' => 'Luas di bawah kurva f(x) dari a sampai b.',
        'meaning' => 'Nilai integral tentu = selisih antiderivatif di b dan a.',
        'example' => null,
        'common_misunderstanding' => null,
        'unit' => null,
        'category' => 'Integral',
        'subcategory' => null,
        'keywords' => json_encode(['integral', 'tentu']),
        'related_concepts' => json_encode([]),
        'related_symbols' => json_encode([]),
        'domain' => 'kalkulus',
        'retrieval_text' => 'integral tentu definite integral luas kurva antiderivatif',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $halaman = [
        'page' => 1,
        'text' => "Halaman berisi materi Bab 4: Integral.\nFORMULA: \int _ { a } ^ { b } f ( x ) \, d x = F ( b ) - F ( a )\nKesimpulan singkat.",
    ];

    $hasil = app(SymbolService::class)->annotateFormulaPages([$halaman]);

    expect($hasil[0]['text'])->toContain('Integral Tertentu')
        ->and($hasil[0]['text'])->toContain('[Simbol]');
});

it('tidak menganotasi formula yang tidak dikenal (bukan substring simbol)', function () {
    DB::table('symbols')->insert([
        'id' => 'calc.int.definite',
        'name' => 'Integral Tertentu',
        'symbol' => null,
        'formula' => null,
        'latex' => '\int_{a}^{b} f(x)\,dx = F(b) - F(a)',
        'definition' => 'Luas di bawah kurva f(x) dari a sampai b.',
        'meaning' => 'Nilai integral tentu = selisih antiderivatif di b dan a.',
        'example' => null,
        'common_misunderstanding' => null,
        'unit' => null,
        'category' => 'Integral',
        'subcategory' => null,
        'keywords' => json_encode(['integral', 'tentu']),
        'related_concepts' => json_encode([]),
        'related_symbols' => json_encode([]),
        'domain' => 'kalkulus',
        'retrieval_text' => 'integral tentu definite integral luas kurva antiderivatif',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $halaman = [
        'page' => 1,
        'text' => "FORMULA: \xi \eta \pi = 3.14159\n",
    ];

    $hasil = app(SymbolService::class)->annotateFormulaPages([$halaman]);

    expect($hasil[0]['text'])->not->toContain('[Simbol]');
});

it('membatasi anotasi simbol pada domain kategori dokumen', function () {
    $halaman = [
        'page' => 1,
        'text' => "FORMULA: pH = -log[H+]\n",
    ];

    // Kategori "Matematika" hanya domain matematika+kalkulus — pH (kimia) tidak relevan.
    $hasil = app(SymbolService::class)->annotateFormulaPages([$halaman], 'Matematika');

    expect($hasil[0]['text'])->not->toContain('[Simbol]');

    // Tanpa batasan kategori, pH tetap dikenali.
    $tanpaKategori = app(SymbolService::class)->annotateFormulaPages([$halaman]);

    expect($tanpaKategori[0]['text'])->toContain('[Simbol]');
});

it('tidak menganotasi saat simbol dimatikan lewat config', function () {
    config(['services.symbols.enabled' => false]);

    $halaman = [
        'page' => 1,
        'text' => "FORMULA: pH = -log[H+]\n",
    ];

    expect(app(SymbolService::class)->annotateFormulaPages([$halaman])[0]['text'])
        ->not->toContain('[Simbol]');
});
