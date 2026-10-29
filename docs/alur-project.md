## 7. Arsitektur Fitur Tugas (3 Lapis)

| Lapis | Isi |
|-------|-----|
| **Front** | Blade `guru/siswa/tugas/*` + `resources/js/pages/tugas-builder.js` (render & tangkap input). Normalisasi lokal di `soal-tugas.js`. |
| **Back** | `Guru|Siswa TugasController` hanya baca input, cek otorisasi, panggil service, kembalikan respons. |
| **Logic** | `app/Services/Tugas/`: `SoalTugasService` (murni array), `PengumpulanTugasService` (submit dengan preservasi file saat resubmission tanpa upload baru, `simpanNilai`, `jadwalkanPemeriksaanAi`), `TugasService` (CRUD + sync pivot), `TugasPresenter` (bentuk siap tampil). |

### Tipe Tugas

`upload` \| `pilihan_ganda` \| `essai`. `essai` = array `[{pertanyaan, referensi?}]`.

### Submit

Siswa submit -> `updateOrCreate` jawaban + dispatch `PeriksaJawabanTugasAI` (hanya bila ada isi).

### Penilaian

`PenilaianAIService::nilai` -> AI JSON `[{indeks,status,skor,alasan}]` -> `hasil_ai` + `nilai_ai` + `koreksi_ai_at`.

### Catatan

- Pivot `tugas_materi`/`kuis_materi` tidak punya timestamp -> jangan `->withTimestamps()`.
- `materi_ids` tidak boleh ikut mass-assignment; pakai `materiPendukung()->sync()`.
- Submit tanpa file baru akan menghapus file lama (`updateOrWrite` file unconditionally) — **BUG diketahui**, jangan memperbaiki tanpa memahami dampak.

## 8. Pendaftaran Siswa ke Kelas + Validasi Operator

**Tujuan:** Siswa mengajukan gabung kelas; guru/operator memverifikasi; keanggotaan aktif di pivot `kelas_siswa.status`.

### Langkah

| No | Nama | Detail |
|----|------|--------|
| 1 | Siswa ajukan gabung | `Siswa\KelasController` -> baris pivot `kelas_siswa` status `pending`. |
| 2 | Operator memvalidasi | `Operator\PendaftaranKelasController` -> setujui/tolak. Kirim notifikasi `PendaftaranKelasDiverifikasi`. |
| 3 | Keanggotaan aktif | `kelas_siswa.status='active'`. Relasi `kelasDiikuti()`/`siswa()` di-scope ke active. |

### Catatan

- `KelasPolicy::enroll()` ada tapi `enroll()` tidak memanggilnya dan tidak cek `kelas.status`.
- `available()`'s search `orWhere` ungrouped — searching by `kode_kelas` can surface ditutup/ineligible classes.
- Self-enrollment dibatasi kelas `status='dibuka'` dan, untuk self_learning jalur, `aturan_kelayakan='terbuka'`.
