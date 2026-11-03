# SIMBA — Agent Guidance

Laravel 13 LMS with AI (RAG tutor, kuis/tugas/remedial, risk scoring + RF). Git repo on `main`, remote `origin`.

## Commands

| Command | Notes |
|---|---|
| `php vendor/bin/pest` | **The test command.** `composer test` fails on host (Redis DNS from Docker `.env`). `php artisan test` needs `php artisan package:discover` first (Collision missing from stale `bootstrap/cache/packages.php`). `phpunit.xml` forces sqlite `:memory:`, sync queue, array session/cache. |
| `php vendor/bin/pest tests/Feature/FooTest.php --filter="…"` | Single file / filtered run. Delete `bootstrap/cache/config.php` if you ran `config:cache`. |
| `composer franken` | Serve with FrankenPHP (port 8000, multi-thread, 3 queue workers inline). Binary hardcoded at `C:\xampp\frankenphp\frankenphp.exe`. |
| `composer serve` / `composer dev` | `serve` is single-threaded — avoid. `dev` adds Vite HMR + workers. |
| `docker compose up -d` | Production stack. `APP_KEY` **required** — `php artisan key:generate --show` then put in `.env` or pass inline. |
| `docker compose build && docker compose up -d` | **Always build after code changes** — images bake source (`./app` is not bind-mounted). `up -d` alone runs stale code. |
| `docker compose exec app php artisan <cmd>` | Run artisan inside container (migrate, ml:train, etc). |

## Critical gotchas (easy to miss)

- **Test DB/cache:** `phpunit.xml` overrides to sqlite `:memory:` + sync/array. `.env`/compose use Redis — host `optimize:clear`/tests fail with Redis DNS errors. Delete `bootstrap/cache/config.php` before testing if you ran `config:cache`.
- **`APP_KEY` mandatory:** Docker production stack fails without it. Generate via `php artisan key:generate --show`.
- **Job `$queue` trap:** Jobs using `Illuminate\Bus\Queueable` must **not** redeclare `public $queue`. `Queueable` already declares it; PHP fatals on autoload (immediate 500 when dispatched). Set queue in constructor via `$this->onQueue('ai-soal')`. Previously hit `GeneratePertemuanJob`, `GenerateSoalTugasAI`, `GenerateSoalKuisAI`.
- **Tesseract path:** Resolved at runtime (`TESSERACT_PATH` env → `shutil.which('tesseract')` → common paths). Was hardcoded to `C:\Program Files\…`, so OCR silently failed in Linux containers.
- **Ollama model from `.env`:** Compose uses `${OLLAMA_MODEL:-qwen2.5:1.5b}` — `.env` wins. Do not pin `qwen2.5:7b` on CPU (4.5× slower, exceeds job timeout). GPU via `docker-compose.gpu.yml` overlay; verify passthrough with `docker run --rm --gpus all alpine sh -c 'ls /dev/nvidia*'`.
- **One image for app + workers:** All app-based services share `simba-app:latest`. Rebuild once to update web + all workers.
- **Queue workers/scheduler healthcheck:** They inherit the image's HTTP `HEALTHCHECK` (`curl /up`) and would be marked unhealthy. Compose sets `healthcheck: disable: true` for them.
- **Host `package:discover` pollutes bind mount:** Writes dev-only providers (Collision, Boost, MCP, Pail) into `bootstrap/cache/packages.php`. The `app` entrypoint rewrites it, but `queue-*`/`scheduler` bypass the entrypoint and can fail on first boot. If they crash: `docker compose exec app php artisan package:discover` then restart them.
- **Route names vs dirs:** Route names are `pengajar.*`/`pelajar.*`/`operator.*`/`pengelola-operator.*` but controller dirs are `guru`/`siswa`. There is **no `admin` role** — the "admin panel" is nested under `operator` prefix gated by `role:operator`.
- **`expectsJson()` in `bootstrap/app.php`:** Required — defining `shouldRenderJsonWhen()` replaces Laravel's default check; without it JSON endpoints 302 instead of 422.
- **`x-form-*` components don't forward `$attributes`:** Only declared props reach the DOM. `x-model`, `wire:*`, `x-on:*` are silently dropped. Fix components (`{{ $attributes }}`) before relying on Alpine bindings.
- **`x-collapse` used in 3 views but `@alpinejs/collapse` not installed:** `x-show` toggles fine; height animation never runs.
- **Essay type is `essai`** (not `esai`) everywhere — enum, validation, views, badges.
- **`materi_ids` must not mass-assign:** Call `$kuis->materiPendukung()->sync(...)` explicitly. Materi utama (nullable FK) vs pendukung (pivot) are different concepts.
- **`whereHas('kelas')->whereHas('siswa')` corrupt correlation** on `BelongsToMany` — references wrong table/column. Use the `kelas_siswa` pivot directly.
- **`tests/Pest.php` applies `RefreshDatabase` globally to Feature tests:** Don't re-add `uses(RefreshDatabase::class)`. Unit tests have no DB unless they opt in.
- **Pest helper functions are global per file:** Use topic-unique prefixes (e.g. `remedialBuatPertemuan`); generic names fatal on full-suite run.
- **`Bus::fake()` for essay submit tests:** With `QUEUE_CONNECTION=sync` the AI job runs inline (~30 s/test). With fake it drops to ~3 s.
- **SQLite hides schema bugs:** Queries on fictitious columns pass in SQLite, fail on MySQL. Add schema contract tests (`Schema::getColumnListing()`/`getIndexes()`).
- **`fitur_siswa_history.tanggal` is `date` cast:** Pass Carbon object (`now()->startOfDay()`), not string, to `where` — string brute-forces a new row under `UNIQUE(siswa_id, pertemuan_id)` in SQLite.
- **`storage/` and `bootstrap/cache/` bind-mounted:** Don't commit them to git. `node_modules` is a named volume for dev.
- **`public/hot` deleted:** Pages use built Vite bundle. `npm run dev` recreates `public/hot`. Vite only on IPv6 `[::1]:5173`.

## AI providers (`app/Services/AIProviderService.php`)

- `send()`/`sendStream()`: Gemini first → Ollama fallbacks. `sendLocalPreferred()`/`sendStreamLocalPreferred()`: Ollama → fallback 1 → Gemini. `sendOllama()` single-model; only caller `ChatService::chat()` is unused.
- **Gemini model is `gemini-3.8-flash`** — do not revert to `gemini-2.0-flash` (404 "no longer available").
- `GEMINI_API_KEY` is in `.env`. When set, `send()` tries Gemini first.
- `GEMINI_SHORT_TIMEOUT=8` in `.env` (config default 30).

## Remedial

- Single trigger per student: `PrediksiController::remedial()` (sync, `set_time_limit(0)`). Mass loop `RemedialService::prosesSiswaBerisiko()` is for scripts/tinker only — a former polling loop produced 319 jobs.
- Real-time triggers in **controllers only** (`Siswa/KuisController::submit`, `Guru/TugasController::nilaiSubmit`), never inside `PengumpulanTugasService::simpanNilai`/`submit` (tests call those directly with `sync` → real AI inline).
- `GenerateRemedianPertemuanAI` implements `ShouldBeUnique` (third idempotency layer). Do not remove.
- `MateriRemedian` needs all pertemuan columns (`pertemuan_id`, `sumber`, `skor_pertemuan`) in `$fillable`.
- Guru lookup via `kelas_siswa` pivot directly — not nested `whereHas`.
- Student name column is `users.name`, not `nama`.

## Risk / ML

- 4 features only: `rata_nilai`, `persen_terlambat`, `persen_terlewat`, `nilai_keseluruhan`. RF is comparison layer (labels from baseline rule), never retrains from itself.
- `ml:train` must not be redeclared in `routes/console.php`.
- SVR `predict()` spawns a libsvm process (~47 ms each) — never call in a metric loop; use `predictBatch()`.
- `prediksi_mendatang` keeps only latest forecast per student (`updateOrCreate(['siswa_id'])`).

## RAG / PDF

- Synchronous ingestion: upload → `ProcessDocument::dispatchSync()` → embed → `document_chunks`. Don't switch to `dispatch()` without strong reason.
- Python 3.11 symlinked as `python3.14` for `DocumentExtractorService`. `pix2tex`/`torch` on CPU unless GPU overlay.
- Documents/chunks carry `kategori`; files save under `documents/{kategori}/`.

## Frontend

- Blade + Tailwind v4 + inline Alpine. No Vue/React/SPA. No `tailwind.config.js` (config via `@import "tailwindcss"` + `@theme` in `app.css`).
- `@json($data)` for JSON payloads, **not** `Js::from()` (breaks `JSON.parse(el.textContent)`).
- Blade `}}` trap in Alpine `x-data`: write `{{ Js::from($x) }} })` (space + `}` before `)`).
- `<script type="application/json">` must be raw JSON via `@json()`.

## Bring Your Own AI (BYO AI)

Pengajar dan pelajar dapat mengkonfigurasi API AI mereka sendiri (OpenAI, Nvidia NIM, Ollama, Gemini, Anthropic, atau endpoint custom) yang akan dipakai untuk generate soal, remedial, dan AI Tutor. Jika tidak dikonfigurasi, sistem fallback ke provider global.

- Tabel: `user_ai_settings` (satu baris per user, `user_id` unique).
- Model: `App\Models\UserAiSetting` (relasi `User::aiSetting()`). API key di-encrypt via `Crypt::encryptString` (jangan simpan plaintext).
- Service: `App\Services\CustomAiService` — generic OpenAI-compatible chat/completions. Support provider: `ollama`, `gemini`, `nvidia_nim`, `openai`, `anthropic`, `custom`.
- Entry point: `AIProviderService::sendForUser(User $user, ...)` — cek setting user dulu, jika enabled & siap pakai (`isReady()`) maka pakai AI user, jika gagal fallback ke `sendLocalPreferred()` (global). Ada juga `sendStreamForUser()` untuk SSE.
- Controller: `App\Http\Controllers\Web\Guru\AiSettingController` — routes `pengajar.ai-settings.*` dan `pelajar.ai-settings.*` (edit, update, test).
- View: `resources/views/guru/ai-settings/edit.blade.php` — form provider, endpoint, API key, model, options (temperature, max_tokens), toggle enabled, dan tombol Test Koneksi.
- Route name: `pengajar.ai-settings.edit` / `pelajar.ai-settings.edit` (controller sama: `Guru\AiSettingController`).

**Integrasi:** Saat memanggil AI di service (KuisGenerator, RemedialTugas, ChatService, PenilaianAI), ganti pemanggilan `$aiProvider->send(...)` menjadi `$aiProvider->sendForUser(auth()->user(), ...)` agar hormati preferensi user.

## Known open defects (don't "fix" by accident)

- `Guru\TugasController::nilaiSubmit` → `RemedialService::periksaSetelahNilaiTugas()` loops every graded student, dispatches `GenerateRemedianPertemuanAI` inline under `sync` — 10–30+ min per POST for a class. Use per-student `PrediksiController::remedial()` instead.
- `Siswa\KuisController::show/submit/hasil` don't enforce `is_aktif`/`tanggal_mulai`/`tanggal_selesai` (only `index()` does).
- `ChatService` persists augmented prompt as `user` message → context accumulates multiplicatively over turns.
- `RemedialController::submit` marks `status='selesai'` before grading; `NilaiRemedianTugasAI` is `tries=1` no `set_time_limit`.
