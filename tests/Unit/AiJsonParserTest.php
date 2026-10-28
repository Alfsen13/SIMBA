<?php

use App\Services\AiJsonParser;

test('mengambil array dari JSON murni', function () {
    $hasil = AiJsonParser::arrayFrom('[{"pertanyaan":"1+1?","pilihan":["1","2","3","4"],"jawaban":0}]');

    expect($hasil)->toBeArray()->toHaveCount(1)
        ->and($hasil[0]['pertanyaan'])->toBe('1+1?')
        ->and($hasil[0]['jawaban'])->toBe(0);
});

test('melepas markdown code fence json', function () {
    $output = "```json\n[{\"pertanyaan\":\"x\",\"pilihan\":[\"a\",\"b\",\"c\",\"d\"],\"jawaban\":2}]\n```";

    expect(AiJsonParser::arrayFrom($output)[0]['jawaban'])->toBe(2);
});

test('mengabaikan prosa sebelum dan sesudah JSON', function () {
    $output = "Berikut soalnya:\n[{\"pertanyaan\":\"y\",\"pilihan\":[\"a\",\"b\",\"c\",\"d\"],\"jawaban\":1}]\nSemoga membantu!";

    expect(AiJsonParser::arrayFrom($output)[0]['pertanyaan'])->toBe('y');
});

test('membuka pembungkus objek menjadi array', function () {
    $output = '{"soal":[{"pertanyaan":"z","pilihan":["a","b","c","d"],"jawaban":3}]}';

    expect(AiJsonParser::arrayFrom($output)[0]['jawaban'])->toBe(3);
});

test('memulihkan escape JSON tidak sah dari LaTeX', function () {
    $output = '[{"pertanyaan":"Hitung \\int x^2 dx","pilihan":["\\frac{x^3}{3}+C"],"jawaban":0}]';

    expect(AiJsonParser::arrayFrom($output)[0]['pertanyaan'])->toBe('Hitung \\int x^2 dx');
});

test('mempertahankan perintah LaTeX yang diawali huruf escape JSON', function (string $perintah) {
    $output = '[{"pertanyaan":"termasuk '.$perintah.' di sini","pilihan":["a","b","c","d"],"jawaban":0}]';

    expect(AiJsonParser::arrayFrom($output)[0]['pertanyaan'])->toBe('termasuk '.$perintah.' di sini');
})->with([
    '\\text', '\\theta', '\\times', '\\tan', '\\frac', '\\beta',
    '\\bar', '\\boxed', '\\nabla', '\\neq', '\\rho', '\\rightarrow',
    '\\begin{aligned}', '\\mathbf{v}', '\\int', '\\sqrt',
]);

test('mode non-LaTeX mempertahankan escape JSON yang sah', function () {
    $valid = json_encode([['pertanyaan' => "L1\nL2\tTab", 'jawaban' => 0]]);

    $hasil = AiJsonParser::arrayFrom($valid, preferLatex: false);

    expect($hasil[0]['pertanyaan'])->toBe("L1\nL2\tTab");
});

test('mode LaTeX memperlakukan backslash sebelum huruf sebagai perintah', function () {
    // Awalan perintah LaTeX (huruf setelah huruf escape) tetap literal.
    $perintah = '[{"pertanyaan":"tolong jelaskan \\neq dan \\tan","pilihan":["a","b","c","d"],"jawaban":0}]';

    expect(AiJsonParser::arrayFrom($perintah)[0]['pertanyaan'])
        ->toBe('tolong jelaskan \\neq dan \\tan');
});

test('mode LaTeX tetap memakai escape huruf bila diikuti spasi', function () {
    // \n diikuti spasi bukan awalan perintah, jadi diperlakukan sebagai baris baru.
    $output = '[{"pertanyaan":"baris \\n baru","pilihan":["a","b","c","d"],"jawaban":0}]';

    expect(AiJsonParser::arrayFrom($output)[0]['pertanyaan'])->toBe("baris \n baru");
});

test('mengubah karakter kontrol mentah di dalam string', function () {
    $output = "[{\"pertanyaan\":\"satu\ndua\ttiga\",\"pilihan\":[\"a\",\"b\",\"c\",\"d\"],\"jawaban\":0}]";

    expect(AiJsonParser::arrayFrom($output)[0]['pertanyaan'])->toBe("satu\ndua\ttiga");
});

test('menangani koma menggantung di akhir array', function () {
    $output = '[{"pertanyaan":"q","pilihan":["a","b","c","d"],"jawaban":0},]';

    expect(AiJsonParser::arrayFrom($output))->toHaveCount(1);
});

test('tidak salah membalik kurung kurawal dan kurung siku di dalam string', function () {
    $output = '[{"pertanyaan":"solve {x^2} dan [y]","pilihan":["a","b","c","d"],"jawaban":0}]';

    expect(AiJsonParser::arrayFrom($output)[0]['pertanyaan'])->toBe('solve {x^2} dan [y]');
});

test('mengembalikan null untuk keluaran yang bukan JSON', function (string $output) {
    expect(AiJsonParser::arrayFrom($output))->toBeNull();
})->with([
    'prosa biasa' => 'Maaf, saya tidak bisa membuat soal.',
    'json terpotong' => '[{"pertanyaan":',
    'string kosong' => '',
]);

test('objectFrom mengembalikan objek dan bukan array', function () {
    $hasil = AiJsonParser::objectFrom('```json
{"indeks":1,"skor":85,"alasan":"ok \\frac{x}{y}"}
```');

    expect($hasil['indeks'])->toBe(1)
        ->and($hasil['skor'])->toBe(85)
        ->and($hasil['alasan'])->toBe('ok \\frac{x}{y}');
});

test('mem-parsing batch soal realistis dengan LaTeX', function () {
    $items = [];

    for ($i = 1; $i <= 12; $i++) {
        $p = $i + 1;

        // Pakai sprintf agar kurung kurawal LaTeX tidak dimakan interpolasi string PHP.
        $items[] = [
            'pertanyaan' => sprintf('Soal ke-%d: hitung \\int x^{%d} dx', $i, $i),
            'pilihan' => [
                sprintf('\\frac{x^{%d}}{%d}+C', $p, $p),
                sprintf('x^{%d}', $i),
                sprintf('%dx', $i),
                '1',
            ],
            'jawaban' => $i % 4,
        ];
    }

    $hasil = AiJsonParser::arrayFrom("```json\n".json_encode($items, JSON_UNESCAPED_SLASHES)."\n```");

    expect($hasil)->toHaveCount(12)
        ->and($hasil[0]['pertanyaan'])->toBe('Soal ke-1: hitung \\int x^{1} dx')
        ->and($hasil[0]['pilihan'][0])->toBe('\\frac{x^{2}}{2}+C')
        ->and($hasil[11]['pertanyaan'])->toBe('Soal ke-12: hitung \\int x^{12} dx');
});

test('memperbaiki kunci objek yang lupa kutip penutup', function () {
    $raw = '[
    {"pertanyaan":"A?","pilihan":["a","b"],"jawaban":1},
    {"pertanyaan":"B?","pilihan":["c","d"],"jawaban:0}]';

    $hasil = AiJsonParser::arrayFrom($raw);

    expect($hasil)->toHaveCount(2)
        ->and($hasil[1]['jawaban'])->toBe(0);
});

test('membuang kutip nyasar setelah nilai primitif', function () {
    $raw = '[
    {"pertanyaan":"A?","pilihan":["a","b"],"jawaban":1"},
    {"pertanyaan":"B?","pilihan":["c","d"],"jawaban":0"}]';

    $hasil = AiJsonParser::arrayFrom($raw);

    expect($hasil)->toHaveCount(2)
        ->and($hasil[1]['jawaban'])->toBe(0);
});

test('memperbaiki dua kerusakan sekaligus dalam satu respons', function () {
    $raw = '[
    {"pertanyaan":"A?","pilihan":["a","b","c","d"],"jawaban":1},
    {"pertanyaan":"B?","pilihan":["e","f","g","h"],"jawaban:1},
    {"pertanyaan":"C?","pilihan":["i","j","k","l"],"jawaban":2"}]';

    $hasil = AiJsonParser::arrayFrom($raw);

    expect($hasil)->toHaveCount(3)
        ->and($hasil[1]['jawaban'])->toBe(1)
        ->and($hasil[2]['jawaban'])->toBe(2);
});

test('repair struktural wajib jalan sebelum escape karakter kontrol', function () {
    // Regresi: kunci rusak "jawaban:1} membuat state machine mengira baris
    // berikutnya masih string. Bila repairRawControlChars jalan lebih dulu,
    // newline asli di-escape menjadi \n di luar JSON dan hasil repair
    // justru tidak valid. Model sering keluarkan JSON pretty-printed
    // bertingkat baris seperti ini, jadi kasus ini wajib lolos.
    $raw = "[\n  {\"pertanyaan\": \"A?\", \"pilihan\": [\"a\", \"b\"], \"jawaban:0},\n  {\"pertanyaan\": \"B?\", \"pilihan\": [\"c\", \"d\"], \"jawaban\": 1}\n]";

    $hasil = AiJsonParser::arrayFrom($raw);

    expect($hasil)->toHaveCount(2)
        ->and($hasil[0]['jawaban'])->toBe(0)
        ->and($hasil[1]['jawaban'])->toBe(1);
});

test('tidak merusak kunci yang benar dan nilai string berlabel waktu', function () {
    $raw = '[{"pertanyaan":"Berapa lama?","jawaban":"waktu: 5 menit","skor":90}]';

    $hasil = AiJsonParser::arrayFrom($raw);

    expect($hasil)->toHaveCount(1)
        ->and($hasil[0]['jawaban'])->toBe('waktu: 5 menit')
        ->and($hasil[0]['skor'])->toBe(90);
});

test('memperbaiki JSON bertingkat baris dengan koma menggantung dan kunci rusak', function () {
    $raw = "```json\n[\n  {\"pertanyaan\": \"A?\", \"pilihan\": [\"a\", \"b\"], \"jawaban\": 0,},\n  {\"pertanyaan\": \"B?\", \"pilihan\": [\"c\", \"d\"], \"jawaban: 1,}\n]\n```";

    $hasil = AiJsonParser::arrayFrom($raw);

    expect($hasil)->toHaveCount(2)
        ->and($hasil[0]['jawaban'])->toBe(0)
        ->and($hasil[1]['jawaban'])->toBe(1);
});
