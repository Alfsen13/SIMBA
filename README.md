# SIMBA — Sistem Informasi Manajemen Belajar Adaptif

**SIMBA** adalah Learning Management System (LMS) berbasis web yang dikembangkan untuk membantu pengelolaan kegiatan pembelajaran sekaligus menyediakan dukungan pembelajaran berbasis *Artificial Intelligence* (AI). Sistem ini menggabungkan pengelolaan kelas dan materi, pemantauan perkembangan peserta didik, serta layanan AI yang memanfaatkan pendekatan *Retrieval-Augmented Generation* (RAG).

> **Catatan:** README ini disusun berdasarkan dokumentasi dan informasi arsitektur SIMBA yang telah dijelaskan selama pengembangan. Detail yang dapat berubah—seperti konfigurasi lingkungan, dependensi Python, variabel `.env`, dan akun awal—tetap perlu disesuaikan dengan isi repositori terbaru.

---

## Daftar Isi

- [Gambaran Umum](#gambaran-umum)
- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Arsitektur Aplikasi](#arsitektur-aplikasi)
- [Alur AI dan Machine Learning](#alur-ai-dan-machine-learning)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi dan Konfigurasi](#instalasi-dan-konfigurasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Pengujian](#pengujian)
- [Struktur Proyek](#struktur-proyek)
- [Keamanan dan Konfigurasi](#keamanan-dan-konfigurasi)
- [Status Pengembangan](#status-pengembangan)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

## Gambaran Umum

SIMBA dikembangkan sebagai LMS yang tidak hanya berfungsi untuk mengelola administrasi pembelajaran, tetapi juga membantu pengajar memahami perkembangan peserta didik melalui analisis data pembelajaran.

Sistem menyediakan fitur pembelajaran dan pengelolaan akademik, serta beberapa komponen AI untuk membantu proses penyusunan evaluasi, pemberian bantuan belajar, pencarian informasi dari dokumen, dan identifikasi peserta didik yang memerlukan perhatian lebih lanjut.

### Tujuan Pengembangan

1. Memusatkan pengelolaan kelas, materi, tugas, dan aktivitas pembelajaran dalam satu sistem.
2. Membantu pengajar memantau perkembangan peserta didik berdasarkan data pembelajaran.
3. Menyediakan asisten pembelajaran berbasis AI yang dapat menggunakan dokumen sebagai sumber informasi.
4. Mendukung penyusunan soal dan tugas dengan bantuan AI.
5. Membantu mengidentifikasi peserta didik yang berpotensi mengalami kesulitan belajar.

## Fitur Utama

### 1. Manajemen Pembelajaran

- Pengelolaan pengguna dan hak akses berdasarkan peran.
- Pengelolaan kelas dan peserta didik.
- Pengelolaan materi dan dokumen pembelajaran.
- Pengelolaan pertemuan pembelajaran.
- Pengelolaan tugas dan kuis.
- Pemantauan aktivitas dan hasil pembelajaran.

SIMBA memiliki empat peran pengguna yang didokumentasikan, yaitu **Operator, Pengelola Operator, Guru, dan Siswa**. Hak akses setiap peran mengikuti aturan yang diterapkan di dalam aplikasi.

### 2. AI Tutor

AI Tutor menyediakan layanan tanya jawab untuk membantu pengguna memahami materi pembelajaran. Sistem dapat memanfaatkan dokumen yang telah diproses sebagai sumber konteks melalui pendekatan RAG.

Komponen yang terlibat antara lain:

- Pemrosesan percakapan melalui layanan AI Tutor.
- Pencarian potongan dokumen yang relevan.
- Penyusunan konteks berdasarkan hasil pencarian.
- Dukungan pemrosesan simbol atau notasi pembelajaran.
- Dukungan pencarian web melalui layanan terkait, sesuai konfigurasi aplikasi.

### 3. Retrieval-Augmented Generation (RAG)

RAG digunakan untuk membantu AI menghasilkan jawaban yang mengacu pada informasi dari dokumen pembelajaran.

Gambaran alur pemrosesan dokumen:

1. Operator mengunggah dokumen.
2. Sistem menjalankan proses ekstraksi dokumen melalui antrean pekerjaan (*queue job*).
3. Teks dokumen diekstraksi menggunakan pipeline pemrosesan.
4. Teks dibagi menjadi beberapa bagian (*chunking*).
5. Sistem menghasilkan embedding untuk setiap bagian teks.
6. Data embedding disimpan bersama potongan dokumen.
7. Ketika pengguna mengajukan pertanyaan, sistem mencari potongan dokumen yang relevan.
8. Hasil pencarian digunakan sebagai konteks untuk menghasilkan jawaban AI.

Embedding menggunakan model **Gemini `text-embedding-004`** sesuai konfigurasi yang didokumentasikan.

### 4. Pembuatan Soal dan Tugas Berbantuan AI

SIMBA menyediakan layanan untuk membantu pengajar menyusun soal kuis dan tugas menggunakan AI. Proses penyusunan soal juga didukung komponen yang berkaitan dengan Taksonomi Bloom agar tingkat kognitif soal dapat disesuaikan dengan kebutuhan pembelajaran.

### 5. Early Warning System (EWS)

Fitur EWS membantu pengajar mengenali peserta didik yang perlu dipantau berdasarkan indikator pembelajaran.

Sistem menggunakan pendekatan *machine learning* untuk klasifikasi tingkat risiko. Kategori yang digunakan dalam rancangan model meliputi:

- **Aman**
- **Perlu Dipantau**
- **Berisiko Tinggi**

Klasifikasi menggunakan Random Forest yang dilatih berdasarkan label baseline dari indikator pembelajaran. Hasil klasifikasi merupakan alat bantu pemantauan, bukan keputusan mutlak mengenai kemampuan peserta didik.

### 6. Prediksi Perkembangan Nilai

SIMBA juga memiliki komponen regresi untuk memperkirakan nilai pada pertemuan berikutnya. Model yang digunakan adalah Support Vector Regression (SVR).

Prediksi mulai dilakukan setelah tersedia data dari tiga pertemuan. Data peserta didik digunakan untuk memprediksi perkembangan nilainya sendiri, bukan dengan mencampurkan data peserta didik lain sebagai data individual.

### 7. Remedial Otomatis

Sistem menyediakan komponen remedial yang membantu menyiapkan tindak lanjut pembelajaran bagi peserta didik yang membutuhkan penguatan materi. Proses ini terhubung dengan layanan remedial dan pembuatan tugas berbantuan AI.

---

## Teknologi

Teknologi utama yang digunakan dalam proyek SIMBA berdasarkan dokumentasi pengembangan:

| Bagian | Teknologi |
|---|---|
| Backend | Laravel 13 |
| Bahasa backend | PHP >= 8.4.1 |
| Frontend | Blade dan Blade Components |
| CSS | Tailwind CSS 4 |
| Interaksi frontend | Alpine.js |
| Asset bundler | Vite |
| Database | MySQL / MariaDB |
| Queue | Laravel Queue (database pada lingkungan pengembangan) |
| Server lokal | FrankenPHP |
| AI dan embedding | Gemini API |
| Machine Learning | PHP-ML |
| Ekstraksi PDF | pdfplumber, PyMuPDF, Tesseract OCR, dan pix2tex |
| Pemrosesan PDF alternatif | smalot/pdfparser |
| Grafik | Chart.js |
| Rendering rumus | KaTeX |
| Pengujian | Pest / PHPUnit |

Versi dan konfigurasi paket dapat dilihat pada `composer.json`, `composer.lock`, `package.json`, dan file konfigurasi terkait di repositori.

## Arsitektur Aplikasi

SIMBA menggunakan arsitektur Laravel dengan pola MVC yang dilengkapi lapisan *Service* dan sistem antrean (*queue*).

Gambaran tanggung jawab komponen:

| Komponen | Tanggung jawab |
|---|---|
| Routes | Mendefinisikan jalur akses aplikasi |
| Controllers | Menerima permintaan dan mengatur alur respons |
| Services | Menangani logika bisnis dan proses utama aplikasi |
| Models | Mengelola representasi data dan relasi database |
| Jobs | Menjalankan proses yang dikerjakan melalui queue |
| Policies | Mengatur pemeriksaan hak akses |
| Middleware | Memeriksa permintaan sebelum diteruskan |
| Notifications | Mengelola pemberitahuan aplikasi |
| Observers | Menangani peristiwa pada model |

Logika bisnis utama ditempatkan pada lapisan Service agar Controller tetap berfokus pada pengelolaan alur permintaan. Proses yang membutuhkan waktu lebih lama, seperti pemrosesan dokumen, dapat dijalankan melalui Queue.

## Alur AI dan Machine Learning

### Pipeline RAG

```text
Dokumen Pembelajaran
        |
        v
Unggah Dokumen
        |
        v
ProcessDocument Job
        |
        v
DocumentExtractorService
        |
        v
Python Extraction Pipeline
        |
        v
Text Chunking
        |
        v
Embedding Generation
        |
        v
Penyimpanan Document Chunks
        |
        v
Retrieval berdasarkan pertanyaan
        |
        v
Konteks untuk AI Tutor
        |
        v
Jawaban
```

### Model Klasifikasi Risiko

Klasifikasi risiko menggunakan Random Forest. Label pelatihan baseline dibentuk dari indikator pembelajaran, kemudian digunakan sebagai target klasifikasi model.

Indikator baseline yang didokumentasikan meliputi:

- Rata-rata nilai.
- Persentase keterlambatan.
- Persentase tugas atau aktivitas yang terlewat.
- Nilai keseluruhan.

Kategori hasil klasifikasi digunakan sebagai informasi pendukung bagi pengajar dalam menentukan peserta didik yang perlu dipantau.

### Model Regresi Nilai

Model SVR digunakan untuk memperkirakan `rata_nilai` pada pertemuan berikutnya. Fitur yang didokumentasikan untuk model ini meliputi:

1. `rata_durasi_belajar`
2. `persen_tugas_terkumpul`
3. `tren_nilai`
4. `persen_kehadiran`
5. `rata_nilai`
6. `jumlah_pertemuan_tidak_aktif`

Evaluasi model menggunakan metrik MAE, RMSE, dan R². Hasil prediksi merupakan estimasi berdasarkan data yang tersedia dan tidak menjamin nilai aktual peserta didik.

---

## Persyaratan Sistem

Sebelum menjalankan SIMBA, siapkan lingkungan berikut:

- PHP **8.4.1 atau lebih baru**.
- Composer.
- MySQL atau MariaDB.
- Node.js dan npm.
- Python 3 untuk pipeline ekstraksi dokumen.
- Tesseract OCR beserta bahasa data yang diperlukan.
- Ekstensi PHP yang dibutuhkan oleh Laravel dan dependensi proyek.
- Git.

Beberapa fitur memerlukan konfigurasi layanan eksternal, terutama API AI dan embedding. Pastikan kredensial layanan tersebut tersedia melalui konfigurasi lingkungan aplikasi.

## Instalasi dan Konfigurasi

### 1. Clone repositori

```bash
git clone https://github.com/Alfsen13/SIMBA.git
cd SIMBA
```

### 2. Instal dependensi PHP

```bash
composer install
```

### 3. Siapkan file lingkungan

Salin `.env.example` menjadi `.env`.

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Konfigurasi database

Buat database MySQL atau MariaDB, kemudian sesuaikan konfigurasi database pada file `.env`.

Contoh konfigurasi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simba
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nama database, username, dan password dengan lingkungan masing-masing.

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Jalankan migrasi database

```bash
php artisan migrate
```

Gunakan seeder jika proyek menyediakan seeder yang memang diperlukan:

```bash
php artisan db:seed
```

### 7. Instal dependensi frontend

```bash
npm install
```

Build aset frontend:

```bash
npm run build
```

### 8. Konfigurasi layanan AI dan ekstraksi dokumen

Lengkapi variabel lingkungan yang diperlukan oleh layanan AI, embedding, dan komponen lain sesuai `.env.example` serta konfigurasi pada source code.

Pipeline ekstraksi dokumen menggunakan Python dan beberapa pustaka pemrosesan PDF/OCR. Pastikan interpreter Python, dependensi pipeline, dan Tesseract OCR tersedia serta dapat diakses oleh aplikasi.

> Nama variabel lingkungan dan perintah instalasi Python harus mengikuti konfigurasi aktual pada repositori. Jangan menggunakan kredensial contoh sebagai kredensial produksi.

### 9. Siapkan storage

```bash
php artisan storage:link
```

Jika aplikasi menggunakan direktori penyimpanan privat, pastikan direktori tersebut tersedia dan memiliki hak akses yang sesuai.

## Menjalankan Aplikasi

### Menjalankan server FrankenPHP

SIMBA didokumentasikan menggunakan FrankenPHP dalam mode klasik untuk lingkungan lokal. Jalankan perintah Composer yang disediakan proyek:

```bash
composer franken
```

Perintah tersebut menggunakan script yang didefinisikan di `composer.json`.

### Menjalankan queue worker

Jika konfigurasi queue menggunakan database, jalankan worker pada terminal terpisah:

```bash
php artisan queue:work
```

Pastikan konfigurasi `QUEUE_CONNECTION` sesuai dengan lingkungan yang digunakan.

### Menjalankan Vite dalam mode pengembangan

Untuk pengembangan frontend:

```bash
npm run dev
```

Gunakan script yang tersedia pada `package.json` jika nama atau konfigurasi perintah berbeda.

---

## Pengujian

Jalankan pengujian aplikasi menggunakan:

```bash
php artisan test
```

Jika proyek menggunakan Pest, jalankan perintah sesuai konfigurasi pengujian yang tersedia.

Pengujian sebaiknya dilakukan setelah konfigurasi database, environment, dan dependensi aplikasi disiapkan.

## Struktur Proyek

Berikut gambaran direktori utama Laravel dan komponen SIMBA:

```text
SIMBA/
├── app/
│   ├── Console/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Jobs/
│   ├── Models/
│   ├── Notifications/
│   ├── Observers/
│   ├── Policies/
│   ├── Providers/
│   ├── Services/
│   └── Python/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
├── package.json
└── README.md
```

Struktur di atas merupakan gambaran direktori utama. Isi dan susunan subdirektori dapat berubah mengikuti perkembangan kode sumber.

## Keamanan dan Konfigurasi

Beberapa hal yang perlu diperhatikan ketika menjalankan atau menerapkan SIMBA:

- Jangan mengunggah file `.env` yang berisi kredensial ke repositori publik.
- Jangan menyimpan API key, token, atau password langsung di source code.
- Pastikan direktori penyimpanan privat tidak dapat diakses langsung oleh pengguna yang tidak berwenang.
- Terapkan hak akses sesuai peran pengguna.
- Gunakan HTTPS pada lingkungan produksi.
- Atur worker queue dan proses Python sesuai kebutuhan server.
- Batasi ukuran dan jenis file yang dapat diunggah.
- Lakukan backup database dan dokumen secara berkala.

## Status Pengembangan

SIMBA dikembangkan secara bertahap. Fitur, konfigurasi, dan dokumentasi dapat berubah mengikuti proses pengembangan dan evaluasi sistem.

Untuk mengetahui implementasi terbaru, lihat source code dan riwayat perubahan pada repositori:

**https://github.com/Alfsen13/SIMBA**

## Kontribusi

Saran, laporan kesalahan (*bug report*), dan masukan pengembangan sangat terbuka. Silakan gunakan fitur Issues atau Pull Requests pada GitHub untuk menyampaikan masukan dan kontribusi.

## Lisensi

Informasi lisensi mengikuti file `LICENSE` yang tersedia di repositori. Jika file lisensi belum tersedia, penggunaan, modifikasi, dan distribusi ulang kode sumber harus dikonfirmasi terlebih dahulu kepada pemilik proyek.

---

<p align="center">
  Dikembangkan sebagai proyek Learning Management System terintegrasi Artificial Intelligence.
</p>
