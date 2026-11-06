<?php

use App\Models\Document;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('returns failure when a document fails but continues processing the rest', function () {
    Storage::fake('public');

    $materi = Materi::factory()->create();
    $user = User::factory()->operator()->create();

    $gagal = Document::create([
        'name' => 'Dokumen gagal',
        'original_name' => 'gagal.txt',
        'path' => 'documents/gagal.txt',
        'status' => 'failed',
        'user_id' => $user->id,
        'materi_id' => $materi->id,
    ]);
    $gagal->chunks()->create(['content' => '   ', 'chunk_index' => 0]);

    $berhasil = Document::create([
        'name' => 'Dokumen berhasil',
        'original_name' => 'berhasil.txt',
        'path' => 'documents/berhasil.txt',
        'status' => 'completed',
        'user_id' => $user->id,
        'materi_id' => $materi->id,
    ]);
    $berhasil->chunks()->create(['content' => 'Ringkasan materi yang valid.', 'chunk_index' => 0]);

    $this->artisan('materi:generate-summaries')
        ->assertExitCode(1);

    $this->assertDatabaseHas('materi_bab_summary', [
        'materi_id' => $materi->id,
        'ringkasan' => 'Ringkasan materi yang valid.',
    ]);
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
