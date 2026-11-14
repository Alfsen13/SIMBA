# Catatan Penting SIMBA

- Jangan `php artisan test` sementara cache konfigurasi ada. `composer test` menjalankan `optimize:clear` lebih dulu, tetapi di host ia gagal (Redis hanya resolve di dalam Docker) — di host pakai `php vendor/bin/pest` setelah menghapus `bootstrap/cache/config.php`.
- Serving = **FrankenPHP**, bukan Apache/XAMPP. XAMPP ships PHP 8.2 yang di bawah requirement `^8.3`.
- Queue: database di dev dengan 4 worker (`ai-soal`, `pertemuan`, `default`, `rumus`) via `composer franken/dev`; sync di tests. Generate soal/tugas **sinkron** di request; generate pertemuan, remedial massal, dan pemindaian rumus via queue.
- Tabel `notifikasis` (bukan `notifications`). Notification `via()` harus hanya mengembalikan `[NotifikasiChannel::class]`.
- Model array casts selalu `json_encode()` pada assignment. Pass array, bukan `json_encode()`.
- Timezone adalah UTC. `now()` jatuh pada hari sebelumnya antara 00:00–06:59 WIB.
- View syntax verifikasi via `php artisan view:cache` — lolos tanpa error.
- AI provider: `send()`/`sendStream()` coba **Gemini pertama** (`GEMINI_API_KEY`), lalu Ollama; `sendLocalPreferred()`/`sendStreamLocalPreferred()` kebalikannya. `sendForUser()` pakai AI pribadi user dulu, jatuh ke `sendLocalPreferred()`. Timeout Ollama 600 detik di semua jalur.
- Model Ollama dari `OLLAMA_MODEL` (default config `simba-tutor`; `.env` dev mengisi `qwen2.5:7b`). `OLLAMA_MODEL_FALLBACK_1/2` default mengikuti `OLLAMA_MODEL` — jangan arahkan ke `qwen2.5-coder:1.5b-base`: itu base model, tidak bisa mengikuti instruksi. Model Gemini `gemini-3.8-flash` dan URL endpoint default disusun dari nama modelnya, jadi `GEMINI_ENDPOINT` hanya diisi untuk menimpa seluruh URL.
- Model custom Ollama dibangun dari `Modelfile`, `Modelfile.full`, atau `Modelfile.slim` di root repo (ketiganya `FROM qwen2.5:7b` dengan parameter berbeda); nama hasilnya ditentukan perintah `ollama create`, bukan isi berkasnya.

## Seeder awal

Seeder utama membuat data referensi, kelas contoh tanpa pengajar atau siswa, satu pengelola operator, dan satu operator per kategori. Akun pengajar dan siswa serta lamaran pengajar tidak dibuat. Seeder tidak menghapus data lama; kelas, mata pelajaran, dan akun yang sudah ada tidak ditimpa.

Setelah membangun ulang citra dan memperbarui container, jalankan di PowerShell:

```powershell
$env:SEED_OPERATOR_PASSWORD = Read-Host "Kata sandi awal akun operator"
docker compose exec -T -e "SEED_OPERATOR_PASSWORD=$env:SEED_OPERATOR_PASSWORD" app php artisan db:seed --force
Remove-Item Env:\SEED_OPERATOR_PASSWORD
```

Variabel tersebut digunakan hanya untuk akun baru; menjalankan seeder ulang tidak mengubah kata sandi akun yang sudah ada. Jika konfigurasi Laravel pernah di-cache, jalankan `docker compose exec app php artisan config:clear` terlebih dahulu. Kata sandi sederhana hanya untuk pengujian lokal dan harus diganti sebelum aplikasi digunakan bersama atau dipublikasikan.
