<?php

use App\Jobs\GeneratePertemuanJob;
use App\Jobs\SaranMateriJob;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\User;
use App\Notifications\GeneratePertemuanGagal;
use App\Services\AIProviderService;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Notification::fake();
});

it('menolak generate pertemuan saat Ollama tidak aktif', function () {
    Queue::fake();

    $this->mock(OllamaService::class)
        ->shouldReceive('isAvailable')
        ->once()
        ->andReturn(false);

    $guru = User::factory()->verified()->pengajar()->create();
    $kelas = Kelas::factory()->for($guru, 'guru')->create();

    $this->actingAs($guru)
        ->from(route('pengajar.kelas.show', $kelas))
        ->post(route('pengajar.kelas.generate-pertemuan', $kelas), [
            'materi_topics' => 'Aljabar, geometri',
            'jumlah_pertemuan' => 8,
        ])
        ->assertRedirect(route('pengajar.kelas.show', $kelas))
        ->assertSessionHas('error', 'Server AI (Ollama) tidak aktif. Nyalakan dulu, lalu coba lagi.');

    Queue::assertNotPushed(GeneratePertemuanJob::class);
});

it('mengenerate pertemuan secara sinkron saat Ollama aktif', function () {
    $this->mock(OllamaService::class)
        ->shouldReceive('isAvailable')
        ->once()
        ->andReturn(true);

    $this->mock(AIProviderService::class)
        ->shouldReceive('sendLocalPreferred')
        ->once()
        ->andReturn(json_encode([
            ['nama_pertemuan' => 'Aljabar Dasar', 'deskripsi' => 'Pengenalan aljabar', 'urutan' => 1],
            ['nama_pertemuan' => 'Geometri', 'deskripsi' => 'Bangun datar', 'urutan' => 2],
            ['nama_pertemuan' => 'Trigonometri', 'deskripsi' => 'Sinus, kosinus', 'urutan' => 3],
        ]));

    $guru = User::factory()->verified()->pengajar()->create();
    $kelas = Kelas::factory()->for($guru, 'guru')->create();

    $this->actingAs($guru)
        ->post(route('pengajar.kelas.generate-pertemuan', $kelas), [
            'materi_topics' => 'Aljabar, geometri, trigonometri',
            'jumlah_pertemuan' => 8,
            'catatan' => 'fokus latihan soal',
        ])
        ->assertRedirect(route('pengajar.kelas.show', $kelas))
        ->assertSessionHas('success');

    expect(Pertemuan::where('kelas_id', $kelas->id)->count())->toBe(3);
    expect(Pertemuan::where('kelas_id', $kelas->id)->orderBy('urutan')->first()->nama_pertemuan)->toBe('Aljabar Dasar');
});

it('menolak generate pertemuan dengan jumlah di luar rentang', function () {
    Queue::fake();

    $this->mock(OllamaService::class)
        ->shouldReceive('isAvailable')
        ->never();

    $guru = User::factory()->verified()->pengajar()->create();
    $kelas = Kelas::factory()->for($guru, 'guru')->create();

    $this->actingAs($guru)
        ->post(route('pengajar.kelas.generate-pertemuan', $kelas), [
            'materi_topics' => 'Aljabar, geometri',
            'jumlah_pertemuan' => 3,
        ])
        ->assertSessionHasErrors('jumlah_pertemuan');
});

it('saran materi tetap async dan tidak dispatch saat Ollama tidak aktif', function () {
    Queue::fake();

    $this->mock(OllamaService::class)
        ->shouldReceive('isAvailable')
        ->once()
        ->andReturn(false);

    $guru = User::factory()->verified()->pengajar()->create();
    $kelas = Kelas::factory()->for($guru, 'guru')->create();

    $this->actingAs($guru)
        ->postJson(route('pengajar.kelas.saran-materi', $kelas))
        ->assertOk()
        ->assertJsonFragment(['status' => 'error']);

    Queue::assertNotPushed(SaranMateriJob::class);
});

it('kirim notifikasi ke guru saat generate pertemuan sinkron gagal', function () {
    $this->mock(OllamaService::class)
        ->shouldReceive('isAvailable')
        ->once()
        ->andReturn(true);

    $this->mock(AIProviderService::class)
        ->shouldReceive('sendLocalPreferred')
        ->once()
        ->andThrow(new RuntimeException('Semua provider AI gagal.'));

    $guru = User::factory()->verified()->pengajar()->create();
    $kelas = Kelas::factory()->for($guru, 'guru')->create();

    $this->actingAs($guru)
        ->from(route('pengajar.kelas.show', $kelas))
        ->post(route('pengajar.kelas.generate-pertemuan', $kelas), [
            'materi_topics' => 'Aljabar',
            'jumlah_pertemuan' => 8,
        ])
        ->assertRedirect(route('pengajar.kelas.show', $kelas))
        ->assertSessionHas('error');

    expect(Pertemuan::where('kelas_id', $kelas->id)->count())->toBe(0);

    Notification::assertSentTo($guru, GeneratePertemuanGagal::class, function ($notif) use ($kelas) {
        return $notif->kelasNama === $kelas->nama_kelas
            && str_contains($notif->alasan, 'Semua provider AI gagal');
    });
});
