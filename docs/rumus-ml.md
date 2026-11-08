# Rumus Machine Learning SIMBA

## Skor Risiko Deterministik

| Item | Nilai |
|------|-------|
| Formula | `skor_penyimpangan = rata-rata(|nilai - baseline|) tiap parameter, bobot setara 0.25` |
| Baseline ideal | [`rata_nilai` 100, `persen_terlambat` 0, `persen_terlewat` 0, `nilai_keseluruhan` 100] |
| Contoh | (70, 20, 10, 75) -> deviasi (30, 20, 10, 25) -> (30+20+10+25)/4 = 21.25 -> `perlu_pantau` |
| Threshold | skor < 20 -> aman; 20 <= skor < 40 -> `perlu_pantau`; skor >= 40 -> `berisiko_tinggi` |
| Null | Salah satu parameter null -> `data_tidak_lengkap`, tidak dihitung skor/kategori |
| Invalid | Salah satu parameter non-numerik atau di luar 0-100 -> `data_tidak_valid`, nilai mentah disimpan (**TIDAK di-clamp**) |

## Regresi SVR

| Item | Nilai |
|------|-------|
| Target | `nilai_pertemuan_berikutnya` (`rata_nilai` baris `fitur_siswa_history` pertemuan n+1) |
| Fitur | `FiturPertemuanService::fiturModel()` = `FEATURES` minus `FEATURE_DIABAIKAN` (`frekuensi_akses_pertemuan`): 6 fitur |
| Model | SVR kernel linear (satu-satunya kandidat di `KANDIDAT`) |
| Normalisasi | 0-100 -> 0-1, median imputasi untuk null |
| Split | `splitGrouped()` per siswa (tidak ada siswa di train dan test sekaligus) |
| Metrik | MAE, RMSE, R2 (skala poin asli 0-100). `predictBatch()` untuk metrik (satu proses libsvm). |
| Clamp | Hasil prediksi di-clamp ke 0-100. |
| Guard | Minimal 30 pasangan per target **DAN** minimal 3 siswa unik. |

### Simulasi sementara

`php artisan ml:train-sintetis-nilai` mengganti model SVR aktif dengan model dari
140 siswa simulasi (980 pasangan), tanpa membuat siswa di database atau melatih
ulang Random Forest. Snapshot disimpan di `ml/dataset_sintetis_nilai.json` pada
disk default. Angka dan siswa pada snapshot seluruhnya sintetis.

Tujuh pola terdiri dari nilai awal tinggi (>= 80) stabil atau menurun; menengah
(60-79) meningkat, stabil, atau menurun; serta rendah (< 60) meningkat atau
stabil. Stabil berarti perubahan per pertemuan maksimal 2 poin. Kategori hanya
menjelaskan data simulasi, bukan keluaran baru pada aplikasi. Nilai dapat
melintasi batas kategori selama perkembangan delapan pertemuan.

Pembagian latih/uji berdasarkan siswa, dengan seluruh pola pada kedua bagian;
median imputasi dihitung dari bagian latih saja. Metrik hanya mengukur simulasi,
bukan akurasi pada pelajar nyata. Pelatihan `ml:train` biasa dapat mengganti
model ini dengan model dari data nyata.

## Random Forest

| Item | Nilai |
|------|-------|
| Model | RandomForest 50 pohon, kedalaman 12 (php-ml) |
| Label | Dari aturan baseline (`aman`/`perlu_pantau`/`berisiko_tinggi`) |
| Fitur | 4 parameter baseline dinormalisasi 0-1 |
| Split | Split per siswa (grouped split) |
| Metrik | Accuracy, macro Precision/Recall/F1, confusion matrix |
| Catatan | Akurasi tinggi berarti "RF meniru aturan baseline", **BUKAN** bukti RF lebih baik. Output RF tidak pernah jadi label latihannya sendiri (`larangan_label`). |

## Cosine Retrieval

| Item | Nilai |
|------|-------|
| Metode | Cosine similarity di PHP |
| Formula | `similarity = dot(A,B) / (||A|| * ||B||)` |
| Truncasi | `cosineSimilarity` truncates to shorter vector (`min(count A, count B)`) — mixing dimensions scores garbage silently |
| Filter | `WHERE kategori = ?` (scope per kategori) |
| Top K | 3 (efektif, bukan default 5) |
