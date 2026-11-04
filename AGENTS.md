# SIMBA — Agent Guidance

Laravel 13 LMS with AI (RAG tutor, kuis/tugas/remedial, risk scoring + RF). Git repo on `main`, remote `origin`.

## Commands

| Command | Notes |
|---|---|
| `php vendor/bin/pest` | The test command. `composer test` fails on host (Redis DNS from Docker `.env`). Delete `bootstrap/cache/config.php` if you ran `config:cache`. |
| `php vendor/bin/pest tests/Feature/FooTest.php --filter="…"` | Single file / filtered run. |
| `composer franken` | Serve with FrankenPHP (port 8000, multi-thread, 3 queue workers inline). Binary at `C:\xampp\frankenphp\frankenphp.exe`. |
| `composer serve` | Single-threaded — avoid for development. |
| `composer dev` | Adds Vite HMR + queue workers. |
| `docker compose up -d` | Production stack. `APP_KEY` **required** — generate via `php artisan key:generate --show` then put in `.env`. |
| `docker compose build && docker compose up -d` | **Always build after code changes** — images bake source (`./app` is not bind-mounted). `up -d` alone runs stale code. |
| `docker compose exec app php artisan <cmd>` | Run artisan inside container (migrate, ml:train, etc). |

## Critical gotchas (agent would likely miss)

- **Test DB/cache:** `phpunit.xml` overrides to sqlite `:memory:` + sync/array. `.env`/compose use Redis — `php artisan optimize:clear` before tests avoids Redis DNS errors. Delete `bootstrap/cache/config.php` if you ran `config:cache`.
- **`APP_KEY` mandatory:** Docker production stack fails without it. Generate via `php artisan key:generate --show` and put in `.env`.
- **Job `$queue` trap:** Jobs using `Illuminate\Bus\Queueable` must **not** redeclare `public $queue`. `Queueable` already declares it; PHP fatals on autoload. Set queue in constructor via `$this->onQueue('ai-soal')`. Previously hit `GeneratePertemuanJob`, `GenerateSoalTugasAI`, `GenerateSoalKuisAI`.
- **`expectsJson()` in `bootstrap/app.php`:** Required — defining `shouldRenderJsonWhen()` replaces Laravel's default check; without it JSON endpoints 302 instead of 422.
- **`x-form-*` components now forward `$attributes`** (fixed). Earlier versions silently dropped `x-model`, `wire:*`, `x-on:*`; current components use `{{ $attributes->except([...]) }}` on inputs. Verify before assuming Alpine bindings work.
- **Essay type is `essai`** (not `esai`) everywhere — enum, validation, views, badges.
- **`materi_ids` must not mass-assign:** Call `$kuis->materiPendukung()->sync(...)` explicitly. Materi utama (nullable FK) vs pendukung (pivot) are different concepts.
- **`whereHas('kelas')->whereHas('siswa')` corrupt correlation** on `BelongsToMany` — references wrong table/column. Use the `kelas_siswa` pivot directly.
- **`tests/Pest.php` applies `RefreshDatabase` globally to Feature tests:** Don't re-add `uses(RefreshDatabase::class)`. Unit tests have no DB unless they opt in.
- **Pest helper functions are global per file:** Use topic-unique prefixes (e.g. `remedialBuatPertemuan`); generic names fatal on full-suite run.
- **`Bus::fake()` for essay submit tests:** With `QUEUE_CONNECTION=sync` the AI job runs inline (~30 s/test). With fake it drops to ~3 s.
- **SQLite hides schema bugs:** Queries on fictitious columns pass in SQLite, fail on MySQL. Add schema contract tests (`Schema::getColumnListing()`/`getIndexes()`).
- **`fitur_siswa_history.tanggal` is `date` cast:** Pass Carbon object (`now()->startOfDay()`), not string, to `where` — string brute-forces a new row under `UNIQUE(siswa_id, pertemuan_id)` in SQLite.
- **`storage/` and `bootstrap/cache/` bind-mounted:** Don't commit them to git.
- **Serving is FrankenPHP, not Apache/XAMPP:** XAMPP ships PHP 8.2 (< required ^8.3). Use `composer franken` or Docker.
- **Timezone is UTC:** `now()` lands on the previous calendar day 00:00–06:59 WIB. Date-based logic needs explicit timezone or `startOfDay()`.
- **Table is `notifikasis`, not `notifications`:** Custom `NotifikasiChannel`; a notification's `via()` must return only `[NotifikasiChannel::class]`.
- **Array-cast model attributes:** assign a PHP array; the cast json_encodes. Do not pass `json_encode()`.
- **Pivots `tugas_materi`/`kuis_materi` have no timestamps:** don't chain `->withTimestamps()`.
- **Dev queue:** database driver with 3 workers (`ai-soal`, `pertemuan`, `default`) via `composer dev`/`franken`; `sync` only in tests. Generate soal/tugas is sync in request; pertemuan + mass remedial go through queue.

## AI providers (`app/Services/AIProviderService.php`)

- `send()`/`sendStream()`: Gemini first → Ollama fallbacks. `sendLocalPreferred()`/`sendStreamLocalPreferred()`: Ollama → fallback → Gemini.
- **Gemini model is `gemini-3.8-flash`** — do not revert to `gemini-2.0-flash` (404 "no longer available").
- `GEMINI_API_KEY` is in `.env`. When set, `send()` tries Gemini first.
- `GEMINI_SHORT_TIMEOUT=8` in `.env` (config default 30).
- **BYO AI integration:** Replace `$aiProvider->send(...)` with `$aiProvider->sendForUser(auth()->user(), ...)` in services (KuisGenerator, RemedialTugas, ChatService, PenilaianAI). This checks user's `user_ai_settings` first; if enabled & ready uses user's AI, otherwise falls back to `sendLocalPreferred()`.

## Remedial

- **Single trigger per student:** `PrediksiController::remedial()` (sync, `set_time_limit(0)`). Mass loop `RemedialService::prosesSiswaBerisiko()` is for scripts/tinker only — a former polling loop produced 319 jobs.
- **Real-time triggers in controllers only** (`Siswa/KuisController::submit`, `Guru/TugasController::nilaiSubmit`), never inside `PengumpulanTugasService::simpanNilai`/`submit` (tests call those directly with `sync` → real AI inline).
- `GenerateRemedianPertemuanAI` implements `ShouldBeUnique` (third idempotency layer). Do not remove.
- `MateriRemedian` needs all pertemuan columns (`pertemuan_id`, `sumber`, `skor_pertemuan`) in `$fillable`.
- **Guru lookup via `kelas_siswa` pivot directly** — not nested `whereHas`.
- **Student name column is `users.name`, not `nama`.**

## Risk / ML

- 4 features only: `rata_nilai`, `persen_terlambat`, `persen_terlewat`, `nilai_keseluruhan`. RF is comparison layer (labels from baseline rule), never retrains from itself.
- `ml:train` must not be redeclared in `routes/console.php`.
- SVR `predict()` spawns a libsvm process (~47 ms each) — never call in a metric loop; use `predictBatch()`.
- `prediksi_mendatang` keeps only latest forecast per student (`updateOrCreate(['siswa_id'])`).

## RAG / PDF

- **Synchronous ingestion:** upload → `ProcessDocument::dispatchSync()` → embed → `document_chunks`. Don't switch to `dispatch()` without strong reason.
- Python 3.11 symlinked as `python3.14` for `DocumentExtractorService`. `pix2tex`/`torch` on CPU unless GPU overlay.
- Documents/chunks carry `kategori`; files save under `documents/{kategori}/`.

## Frontend

- Blade + Tailwind v4 + inline Alpine. No Vue/React/SPA. No `tailwind.config.js` (config via `@import "tailwindcss"` + `@theme` in `app.css`).
- **`@json($data)` for JSON payloads, NOT `Js::from()`** (breaks `JSON.parse(el.textContent)`).
- **Blade `}}` trap in Alpine `x-data`:** write `{{ Js::from($x) }} })` (space + `}` before `)`).
- `<script type="application/json">` must be raw JSON via `@json()`.

## Known open defects (don't "fix" by accident)

- `Guru\TugasController::nilaiSubmit` → `RemedialService::periksaSetelahNilaiTugas()` loops every graded student, dispatches `GenerateRemedianPertemuanAI` inline under `sync` — 10–30+ min per POST for a class. Use per-student `PrediksiController::remedial()` instead.
- `Siswa\KuisController::show/submit/hasil` don't enforce `is_aktif`/`tanggal_mulai`/`tanggal_selesai` (only `index()` does).
- `ChatService` persists augmented prompt as `user` message → context accumulates multiplicatively over turns.
- `RemedialController::submit` marks `status='selesai'` before grading; `NilaiRemedianTugasAI` is `tries=1` no `set_time_limit`.
- High-impact extras: `PengumpulanTugasService::submit` preserves old file on resubmission; `KuisGeneratorService` casts answer with `(int)` only (letter keys → 0); `MateriObserver` accepts doc/ppt/xls but `ProcessDocument` rejects `.doc`.

## References

- `docs/arsitektur.md` — layer/service map, roles, flowcharts.
- `docs/known-defects.md` — open defects with severity.
- `docs/catatan-penting.md` — operational notes.
- `docs/alur-project.md` — feature flows (tugas, pendaftaran).
- `README.md` — install/run (generic).