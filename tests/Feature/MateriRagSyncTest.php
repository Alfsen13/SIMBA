<?php

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\EmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\mock;

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

it('mempertahankan file materi lama jika penyimpanan pengganti gagal', function () {
    Bus::fake();
    Storage::fake('public');

    $guru = mrssGuru();
    $kelas = mrssKelas($guru);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
        'file_path' => 'materi/lama.pdf',
        'file_type' => 'pdf',
    ]);
    Storage::disk('public')->put('materi/lama.pdf', 'berkas lama');

    $disk = Mockery::mock(Storage::disk('public'))->makePartial();
    $disk->shouldReceive('putFileAs')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    $this->actingAs($guru)->put(route('pengajar.materi.update', $materi), [
        'judul' => $materi->judul,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'file' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
    ])->assertServerError();

    expect($materi->fresh()->file_path)->toBe('materi/lama.pdf')
        ->and(Storage::disk('public')->exists('materi/lama.pdf'))->toBeTrue();
});

it('memberi tahu bahwa materi tersimpan tetapi gagal diproses untuk AI Tutor', function () {
    Storage::fake('public');

    $guru = mrssGuru();
    $kelas = mrssKelas($guru);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    mock(DocumentService::class)
        ->shouldReceive('chunkText')
        ->once()
        ->andReturn([['content' => 'konten materi', 'index' => 0]]);
    mock(EmbeddingService::class)
        ->shouldReceive('embedBatch')
        ->once()
        ->andThrow(new RuntimeException('embedding gagal uji'));

    $response = $this->actingAs($guru)->post(route('pengajar.materi.store'), [
        'judul' => 'Materi Uji',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'file' => UploadedFile::fake()->createWithContent('materi.txt', 'konten materi'),
    ]);

    $materi = Materi::where('judul', 'Materi Uji')->firstOrFail();
    $document = Document::where('materi_id', $materi->id)->firstOrFail();

    $response->assertRedirect(route('pengajar.pertemuan.show', $pertemuan))
        ->assertSessionHas('success')
        ->assertSessionHas('warning', fn (string $warning) => str_contains($warning, 'embedding gagal uji'));
    expect($document->status)->toBe('failed');

    $this->get(route('pengajar.materi.show', $materi))
        ->assertOk()
        ->assertSee('embedding gagal uji');

    $operator = User::factory()->operator()->create([
        'kategori_mapel' => $kelas->kategori,
    ]);

    $this->actingAs($operator)
        ->get(route('operator.materi.show', $materi))
        ->assertOk()
        ->assertSee('embedding gagal uji');
});

it('menghapus ringkasan lama jika file materi pengganti gagal diproses', function () {
    Storage::fake('public');

    $guru = mrssGuru();
    $kelas = mrssKelas($guru);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    Document::create([
        'name' => 'Materi lama',
        'original_name' => 'lama.txt',
        'path' => 'materi/lama.txt',
        'type' => 'txt',
        'status' => 'completed',
        'user_id' => $guru->id,
        'materi_id' => $materi->id,
        'kelas_id' => $kelas->id,
    ]);
    MateriBabSummary::create([
        'materi_id' => $materi->id,
        'ringkasan' => 'Ringkasan dari file lama.',
    ]);

    mock(DocumentService::class)
        ->shouldReceive('chunkText')
        ->once()
        ->andReturn([['content' => 'konten file baru', 'index' => 0]]);
    mock(EmbeddingService::class)
        ->shouldReceive('embedBatch')
        ->once()
        ->andThrow(new RuntimeException('embedding gagal uji'));

    $this->actingAs($guru)->put(route('pengajar.materi.update', $materi), [
        'judul' => $materi->judul,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'file' => UploadedFile::fake()->createWithContent('baru.txt', 'konten file baru'),
    ])->assertRedirect(route('pengajar.pertemuan.show', $pertemuan));

    expect(MateriBabSummary::where('materi_id', $materi->id)->exists())->toBeFalse()
        ->and(Document::where('materi_id', $materi->id)->value('status'))->toBe('failed');
});

it('menghapus teks hasil ekstraksi saat file materi diganti', function () {
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
    Document::create([
        'name' => 'Materi lama',
        'original_name' => 'lama.txt',
        'path' => 'materi/lama.txt',
        'type' => 'txt',
        'status' => 'completed',
        'user_id' => $guru->id,
        'materi_id' => $materi->id,
        'kelas_id' => $kelas->id,
        'kategori' => $kelas->kategori,
    ]);
    $kategoriPath = 'summaries/'.$kelas->kategori.'/materi_'.$materi->id.'.txt';
    $legacyPath = 'summaries/materi_'.$materi->id.'.txt';
    Storage::disk('public')->put($kategoriPath, 'teks lama');
    Storage::disk('public')->put($legacyPath, 'teks lama');
    Storage::disk('public')->put('summaries/tidak-terkait.txt', 'tetap ada');
    Storage::disk('public')->put('materi/baru.txt', 'teks baru');

    $materi->update([
        'file_path' => 'materi/baru.txt',
        'file_type' => 'txt',
    ]);

    expect(Storage::disk('public')->exists($kategoriPath))->toBeFalse()
        ->and(Storage::disk('public')->exists($legacyPath))->toBeFalse()
        ->and(Storage::disk('public')->exists('summaries/tidak-terkait.txt'))->toBeTrue()
        ->and(Document::where('materi_id', $materi->id)->count())->toBe(1);
});

it('menghapus teks hasil ekstraksi saat materi dihapus', function () {
    Storage::fake('public');

    $guru = mrssGuru();
    $kelas = mrssKelas($guru);
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'file_path' => null,
        'file_type' => null,
    ]);
    Document::create([
        'name' => 'Materi',
        'original_name' => 'materi.txt',
        'path' => 'materi/materi.txt',
        'type' => 'txt',
        'status' => 'completed',
        'user_id' => $guru->id,
        'materi_id' => $materi->id,
        'kelas_id' => $kelas->id,
        'kategori' => $kelas->kategori,
    ]);
    $summaryPath = 'summaries/'.$kelas->kategori.'/materi_'.$materi->id.'.txt';
    Storage::disk('public')->put($summaryPath, 'teks ekstraksi');

    $materi->delete();

    expect(Storage::disk('public')->exists($summaryPath))->toBeFalse()
        ->and(Document::where('materi_id', $materi->id)->exists())->toBeFalse();
});
