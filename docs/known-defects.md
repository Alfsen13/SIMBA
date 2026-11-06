# Known Defects SIMBA

## High

| # | Defek | Detail |
|---|-------|--------|
| H1 | `Guru\TugasController::nilaiSubmit` -> pemeriksaan remedial pascapenilaian | **Fixed:** request now dispatches a unique background scan job; per-student remedial generation stays on the queue. |
| H2 | `ChatSessionService` enforces ownership | `assertOwned`/`end`/`getSessionHistory` scoped by user; all chat routes require `auth:sanctum` with `throttle:chat` and validated uuid/max length (`SendChatRequest` max:2000). |
| H3 | `PengumpulanTugasService::submit` | **Fixed:** replacing a submission clears stale grading and deletes an old upload when it is replaced or omitted. |
| H4 | `KuisGeneratorService` jawaban | **Fixed:** parser accepts and maps A-D answer keys, and rejects invalid indices. |
| H5 | `DocumentExtractorService` | **Fixed:** Python PDF extraction enforces `services.pdf.timeout` and terminates the child process on timeout. |

## Medium

| # | Defek | Detail |
|---|-------|--------|
| M1 | `Siswa\KuisController` | **Fixed:** daftar, buka, dan submit kuis now enforce active status and schedule; an enrolled student can still view their own submitted result after the quiz closes. |
| M2 | `KuisRequest`/`TugasRequest`/`MateriRequest` and update requests | **Fixed:** class IDs must belong to the authenticated teacher, and session IDs must belong to the selected class. |
| M3 | `Admin\UserController::verify/reject` | **Fixed:** the `User` model allows and casts verification audit fields; feature tests assert the operator and timestamp are saved. |
| M5 | `RemedialController::submit` | **Fixed:** status becomes complete only after successful grading; the unique grading job retries failures. |
| M6 | `ProcessDocument` / `EmbeddingService::searchChunks` | **Fixed:** failed document processing removes partial chunks, and RAG search only returns chunks from completed documents. |
| M7 | `UsersImport` | **Fixed:** imported rows require an 8-character password and a student/teacher role, validate required fields and column order, and have a matching user template. |

## Low

| # | Defek | Detail |
|---|-------|--------|
| L1 | `MateriObserver` / materi upload requests | **Fixed:** upload validation and RAG ingestion now allow only formats handled by `ProcessDocument`. |
| L2 | `WebSearchService` | **Fixed:** DuckDuckGo requests use the HTTP client's default TLS certificate verification. |
| L3 | `DatasetLatihService` | **Fixed:** snapshots are written to a temporary file and moved into place only after a complete write. |
| L4 | `config/services.php` | **Fixed:** the default `rag.chunk_overlap` now matches the 200-token fallback in document processing. |
| L5 | `ImportRequest` | **Fixed:** user and class imports accept only CSV/TXT and XLSX, which match the available parsers. |
| L6 | `prediksi_mendatang` | **By design:** stores only each student's latest forecast via `updateOrCreate(['siswa_id'])`. |
