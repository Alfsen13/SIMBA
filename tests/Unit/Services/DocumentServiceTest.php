<?php

use App\Services\DocumentService;

test('memecah paragraf panjang tanpa menghasilkan chunk kosong atau melewati batas', function () {
    $chunks = app(DocumentService::class)->chunkText('abcdefghijklmnopqrst', chunkSize: 8, overlap: 2);

    expect(array_column($chunks, 'content'))->toBe([
        'abcdefgh',
        'ghijklmn',
        'mnopqrst',
    ]);

    foreach ($chunks as $chunk) {
        expect($chunk['content'])->not->toBeEmpty()
            ->and(mb_strlen($chunk['content']))->toBeLessThanOrEqual(8);
    }
});

test('mempertahankan metadata halaman saat paragraf PDF panjang dipecah', function () {
    $chunks = app(DocumentService::class)->chunkTextWithPages(
        [['page' => 3, 'text' => 'abcdefghijklmnopqrst']],
        chunkSize: 8,
        overlap: 2,
    );

    expect($chunks)->toHaveCount(3);

    foreach ($chunks as $chunk) {
        expect($chunk['content'])->not->toBeEmpty()
            ->and(mb_strlen($chunk['content']))->toBeLessThanOrEqual(8)
            ->and($chunk['page_start'])->toBe(3)
            ->and($chunk['page_end'])->toBe(3);
    }
});
