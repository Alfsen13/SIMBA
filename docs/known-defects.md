# Known Defects SIMBA

## High

| # | Defek | Detail |
|---|-------|--------|
| H1 | `Guru\TugasController::nilaiSubmit` -> `RemedialService::periksaSetelahNilaiTugas()` | Loops every graded student, dispatching `GenerateRemedialPertemuanAI` inline (35-65s each) with no `set_time_limit(0)`. |
| H2 | `ChatSessionService` enforces ownership | `assertOwned`/`end`/`getSessionHistory` scoped by user; all chat routes require `auth:sanctum` with `throttle:chat` and validated uuid/max length (`SendChatRequest` max:2000). |
| H3 | `PengumpulanTugasService::submit` | Preserves existing file on resubmission without a new upload. |
| H4 | `KuisGeneratorService` casts jawaban with `(int)` only | Letter keys ("B"/"C") silently mean option 0. |
| H5 | `DocumentExtractorService` | Enforces `services.pdf.timeout` via `proc_open`/`stream_select` with termination on timeout. |

## Medium

| # | Defek | Detail |
|---|-------|--------|
| M1 | `Siswa\KuisController::show/submit/hasil` | Check enrollment + `pertemuan.dibuka` only. `is_aktif`, `tanggal_mulai`, `tanggal_selesai` enforced in `index()` but not on direct-URL paths. |
| M2 | `UpdateKuisRequest`/`UpdateTugasRequest`/`UpdateMateriRequest` | Validate `kelas_id`/`pertemuan_id` with `exists:` only. Teacher can re-parent their own kuis/tugas/materi into another teacher's class. |
| M3 | `Admin\UserController::verify/reject` | Writes `verified_by`/`verified_at` which are not fillable. |
| M4 | `ChatService` | Persists augmented prompt as user message, then passes raw message again plus that history row — context accumulates multiplicatively. |
| M5 | `RemedialController::submit` | Marks `status='selesai'` before grading; `NilaiRemedialTugasAI` is tries=1 with no `set_time_limit(0)`. |

## Low

| # | Defek | Detail |
|---|-------|--------|
| L1 | `MateriObserver::SUPPORTED_TYPES` | Accepts doc/ppt/xls, but `ProcessDocument` rejects `.doc` and has no branch for ppt/xls. |
| L2 | `WebSearchService` | Disables TLS verification for DuckDuckGo. |
| L3 | `DatasetLatihService` | Writes snapshot non-atomically; crash mid-write leaves truncated JSON. |
| L4 | `config/services.php` | Defaults `rag.chunk_overlap` to 100 while `ProcessDocument`/`DocumentService` default to 200. |
| L5 | `ImportRequest` | Accepts `.xls`/`.txt` but `UsersImport` treats anything not xlsx as CSV. |
| L6 | `prediksi_mendatang` | Keeps only each student's latest forecast (`updateOrCreate ['siswa_id']`). |
