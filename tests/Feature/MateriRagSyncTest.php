<?php

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// Prefix helper unik per file (fungsi global di Pest) — jangan diganti generik.
function mrssGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function mrssKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

it('memproses RAG otomatis saat materi dengan file didaftarkan', function () {
    Bus::fake();
    Storage::fake('public');

    $guru = mrssGuru();
    $kelas = mrssKelas($guru);

    Storage::disk('public')->put('materi/ikatan-kimia.pdf', '%PDF-1.4 dummy');

    $materi = Materi::factory()->create([
        'judul' => 'Ikatan Kimia',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'file_path' => 'materi/ikatan-kimia.pdf',
        'file_type' => 'pdf',
    ]);

    $document = Document::where('materi_id', $materi->id)->first();

    expect($document)->not->toBeNull()
        ->and($document->path)->toBe('materi/ikatan-kimia.pdf')
        ->and($document->kategori)->toBe($kelas->kategori)
        ->and($document->kelas_id)->toBe($kelas->id);

    Bus::assertDispatched(
        ProcessDocument::class,
        fn (ProcessDocument $job) => $job->document->materi_id === $materi->id,
    );
});

it('tidak memproses RAG saat materi tanpa file', function () {
    Bus::fake();
    Storage::fake('public');

    $guru = mrssGuru();
    $kelas = mrssKelas($guru);

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'file_path' => null,
        'file_type' => null,
    ]);

    expect(Document::where('materi_id', $materi->id)->exists())->toBeFalse();

    Bus::assertNotDispatched(ProcessDocument::class);
});
