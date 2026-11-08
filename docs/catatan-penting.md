# Catatan Penting SIMBA

- `composer test`, **jangan** bare `php artisan test`, sementara cache ada. `composer test` menjalankan `optimize:clear` terlebih dahulu.
- Serving = **FrankenPHP**, bukan Apache/XAMPP. XAMPP ships PHP 8.2 yang di bawah requirement `^8.3`.
- Queue: database di dev dengan 3 worker (`ai-soal`, `pertemuan`, `default`) via `composer franken/dev`; sync di tests. Generate soal/tugas **sinkron** di request; generate pertemuan dan remedial massal via queue.
- Tabel `notifikasis` (bukan `notifications`). Notification `via()` harus hanya mengembalikan `[NotifikasiChannel::class]`.
- Model array casts selalu `json_encode()` pada assignment. Pass array, bukan `json_encode()`.
- Timezone adalah UTC. `now()` jatuh pada hari sebelumnya antara 00:00–06:59 WIB.
- View syntax verifikasi via `php artisan view:cache` — lolos tanpa error.
- AI provider: `send()` coba **Gemini pertama** (`GEMINI_API_KEY` di `.env`), fallback Ollama (`OLLAMA_MODEL` di `.env`, default `qwen2.5:1.5b`). Timeout Ollama 1200s (20 menit) untuk model besar (phi4-mini).

## Seeder awal

Seeder utama membuat data referensi, kelas contoh tanpa pengajar atau siswa, satu pengelola operator, dan satu operator per kategori. Akun pengajar dan siswa serta lamaran pengajar tidak dibuat. Seeder tidak menghapus data lama; kelas, mata pelajaran, dan akun yang sudah ada tidak ditimpa.

Setelah membangun ulang citra dan memperbarui container, jalankan di PowerShell:

```powershell
$env:SEED_OPERATOR_PASSWORD = Read-Host "Kata sandi awal akun operator"
docker compose exec -T -e "SEED_OPERATOR_PASSWORD=$env:SEED_OPERATOR_PASSWORD" app php artisan db:seed --force
Remove-Item Env:\SEED_OPERATOR_PASSWORD
```

Variabel tersebut digunakan hanya untuk akun baru; menjalankan seeder ulang tidak mengubah kata sandi akun yang sudah ada. Jika konfigurasi Laravel pernah di-cache, jalankan `docker compose exec app php artisan config:clear` terlebih dahulu. Kata sandi sederhana hanya untuk pengujian lokal dan harus diganti sebelum aplikasi digunakan bersama atau dipublikasikan.
