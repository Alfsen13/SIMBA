# Panduan Copilot untuk SIMBA

SIMBA adalah LMS Laravel 13 dengan Blade, Alpine.js, Tailwind CSS v4, dan Vite.
Ikuti juga `AGENTS.md` di root; dokumen itu berisi panduan domain dan daftar
masalah yang diketahui. Periksa implementasi saat ini jika dokumentasi berbeda.

## Menjalankan dan memeriksa

- Dependensi: `composer install` dan `npm ci`. Versi PHP harus memenuhi
  `composer.lock`; dependensi saat ini membutuhkan PHP >= 8.4.1.
- Jalankan tes: `php vendor/bin/pest`. Satu file atau kasus:
  `php vendor/bin/pest tests/Feature/PrediksiSinkronTest.php --filter="endpoint status"`.
- Format PHP: `php vendor/bin/pint --test`; hilangkan `--test` untuk menerapkan
  format. Build aset: `npm run build`. `npm run dev` menjalankan Vite HMR.
- `phpunit.xml` memakai SQLite in-memory, cache/session array, dan queue sync.
  Jangan memakai cache konfigurasi produksi saat menjalankan tes. `composer test`
  menjalankan `optimize:clear` terlebih dahulu dan dapat gagal di host jika
  konfigurasi Redis hanya dapat diresolusikan di jaringan Docker.
- Jika PHP host tidak cocok, dari PowerShell jalankan tes dengan image aplikasi
  dan `vendor` pengembangan yang terpasang di checkout:
  `docker run --rm --entrypoint php -e APP_ENV=testing -v "${PWD}:/repo" -w /repo simba-app:latest vendor/bin/pest tests/Feature/PrediksiSinkronTest.php`.
  `APP_ENV=testing` diperlukan karena image menetapkan `production`.
- `composer franken` menjalankan FrankenPHP dan tiga worker; skrip ini memakai
  path FrankenPHP khusus Windows. `composer dev` juga menjalankan Vite dan worker.
  Untuk Docker, siapkan `APP_KEY` di environment, lalu `docker compose up -d`.
  Setelah mengubah kode, jalankan `docker compose build` dan `docker compose up -d`:
  source dibundel di image, bukan di-bind-mount.
- Docker memakai MySQL dan Redis; `storage` dan `bootstrap/cache` di-bind-mount.
  Jangan jalankan seeder, migrasi destruktif, atau training pada database yang
  digunakan pengguna tanpa memeriksa dampaknya. Ollama dapat berjalan di host
  melalui `host.docker.internal`; periksa konfigurasi sebelum mengubah provider.

## Alur arsitektur

- `routes/web.php` dan `routes/api.php` menghubungkan controller per peran ke
  service domain dan Eloquent. Policy mengatur otorisasi; `bootstrap/app.php`
  memasang middleware role, locale, Sanctum, dan rendering JSON untuk API maupun
  request `expectsJson()`. Peran utama: operator, pengelola operator, pengajar,
  dan pelajar.
- Materi yang memiliki file dideteksi `MateriObserver`, lalu dibuat `Document`
  dan diproses dengan `ProcessDocument::dispatchSync()` menjadi chunks dan
  embedding. `RAGService` mengambil konteks berdasarkan kategori dan, jika
  tersedia, materi atau pertemuan. Perubahan lifecycle materi harus menjaga
  sinkronisasi dokumen, chunks, dan artefak ringkasan.
- `AIProviderService` memiliki jalur Gemini-first (`send`) dan Ollama-first
  (`sendLocalPreferred`). `sendForUser` mencoba pengaturan AI pengguna yang siap
  terlebih dahulu, lalu fallback ke jalur lokal. Pertahankan pilihan provider
  dan perilaku fallback pada call site yang diubah.
- Generate soal kuis/tugas berjalan sinkron; generate pertemuan dan remedial
  massal memakai queue. Prediksi risiko dijadwalkan sebagai `RunPrediksiJob`
  unik pada queue `default`, dengan status cache yang dipoll halaman prediksi.
  Pastikan proses yang membaca model memakai disk filesystem yang sama.
- `PrediksiRisikoService` menentukan kategori acuan dengan skor baseline dari
  empat fitur akademik. Random Forest meniru label baseline sebagai pembanding,
  bukan penentu risiko; SVR memprediksi nilai pertemuan berikutnya dari fitur
  pertemuan. Split evaluasi dikelompokkan per siswa. Artefak model berada di
  `ml` pada disk Laravel default, yang dapat berbeda antara host dan Docker.
  `ml:train --dataset` tetap menghitung dan menulis data turunan database, dan
  kegagalan model dapat hanya menjadi warning; verifikasi laporan dan artefaknya.

## Konvensi yang perlu dijaga

- `tests/Pest.php` sudah menerapkan `RefreshDatabase` ke semua Feature test.
  Helper Pest di tingkat file bersifat global; gunakan prefiks topik yang unik.
  Gunakan fake queue/bus atau HTTP pada tes alur yang dapat memanggil AI.
- Tipe esai disimpan sebagai `essai`. `materi_ids` harus disinkronkan melalui
  `materiPendukung()->sync()`, bukan mass assignment; pivot `tugas_materi` dan
  `kuis_materi` tidak memiliki timestamp.
- Keanggotaan kelas aktif berada pada pivot `kelas_siswa.status`. Gunakan pivot
  tersebut untuk query korelasi siswa dan kelas yang rentan salah pada
  `whereHas` bertingkat. Atribut model dengan array cast menerima array PHP.
- Notifikasi aplikasi memakai `NotifikasiChannel` dan tabel `notifikasis`,
  bukan channel database Laravel. Job dengan `Queueable` tidak boleh
  mendeklarasikan ulang properti `$queue`; gunakan `onQueue()` di constructor.
- Tailwind v4 dikonfigurasi di `resources/css/app.css`, tanpa
  `tailwind.config.js`. Untuk `<script type="application/json">`, gunakan JSON
  mentah dengan `@json()`. Untuk nilai JSON di atribut HTML Alpine, lakukan
  encoding JSON sekaligus escaping atribut agar tanda kutip tidak memutus HTML.
- Rujukan tambahan: `docs/arsitektur.md`, `docs/alur-project.md`,
  `docs/rumus-ml.md`, dan `docs/known-defects.md`.
