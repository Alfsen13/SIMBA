<?php

use App\Http\Controllers\Web\Admin\BeritaController;
use App\Http\Controllers\Web\Admin\DocumentController as AdminDocument;
use App\Http\Controllers\Web\Admin\KelasController as AdminKelas;
use App\Http\Controllers\Web\Admin\UserController as AdminUser;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Web\Guru\FiturSiswaController;
use App\Http\Controllers\Web\Guru\KehadiranController as GuruKehadiran;
use App\Http\Controllers\Web\Guru\KelasController as GuruKelas;
use App\Http\Controllers\Web\Guru\KuisController;
use App\Http\Controllers\Web\Guru\MateriController;
use App\Http\Controllers\Web\Guru\NilaiController as GuruNilai;
use App\Http\Controllers\Web\Guru\PertemuanController;
use App\Http\Controllers\Web\Guru\PrediksiController;
use App\Http\Controllers\Web\Guru\RemedialController as GuruRemedial;
use App\Http\Controllers\Web\Guru\TugasController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\NotifikasiController;
use App\Http\Controllers\Web\Operator\DashboardController as OperatorDashboard;
use App\Http\Controllers\Web\Operator\ImportController as OperatorImport;
use App\Http\Controllers\Web\Operator\MataPelajaranController as OperatorMataPelajaran;
use App\Http\Controllers\Web\Operator\PendaftaranKelasController as OperatorPendaftaran;
use App\Http\Controllers\Web\Operator\PengajuanMengajarController as OperatorPengajuan;
use App\Http\Controllers\Web\Operator\PengajuanPerubahanPendidikanController as OperatorPengajuanPerubahan;
use App\Http\Controllers\Web\PengelolaOperator\DashboardController as PengelolaOperatorDashboard;
use App\Http\Controllers\Web\PengelolaOperator\KategoriMapelController as PengelolaOperatorKategoriMapel;
use App\Http\Controllers\Web\PengelolaOperator\OperatorController as PengelolaOperatorManage;
use App\Http\Controllers\Web\Siswa\AITutorController;
use App\Http\Controllers\Web\Siswa\AktivitasController as SiswaAktivitas;
use App\Http\Controllers\Web\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\Web\Siswa\KelasController as SiswaKelas;
use App\Http\Controllers\Web\Siswa\KuisController as SiswaKuis;
use App\Http\Controllers\Web\Siswa\MateriController as SiswaMateri;
use App\Http\Controllers\Web\Siswa\NilaiController as SiswaNilai;
use App\Http\Controllers\Web\Siswa\PengajuanPerubahanPendidikanController as SiswaPengajuanPerubahan;
use App\Http\Controllers\Web\Siswa\PertemuanController as SiswaPertemuan;
use App\Http\Controllers\Web\Siswa\RemedialController as SiswaRemedial;
use App\Http\Controllers\Web\Siswa\TugasController as SiswaTugas;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/berita/{beritum}', [HomeController::class, 'show'])->name('berita.show');

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/login', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/verifikasi-pending', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->status === 'verified') {
        return match ($user->role) {
            'pengelola_operator' => redirect()->route('pengelola-operator.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            'pengajar' => redirect()->route('pengajar.dashboard'),
            'pelajar' => redirect()->route('pelajar.dashboard'),
            default => redirect()->route('home'),
        };
    }

    if ($user->role === 'operator' || $user->role === 'pengelola_operator') {
        return match ($user->role) {
            'pengelola_operator' => redirect()->route('pengelola-operator.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            default => redirect()->route('home'),
        };
    }

    return view('auth.pending');
})->middleware(['auth'])->name('pending');

Route::prefix('pengajar')->name('pengajar.')->middleware(['auth', 'role:pengajar'])->group(function () {
    Route::get('/', [GuruDashboard::class, 'index'])->name('dashboard');
    Route::get('kelas/available', [GuruKelas::class, 'available'])->name('kelas.available');
    Route::get('kelas/{kelas}/ajukan', [GuruKelas::class, 'formAjukan'])->name('kelas.ajukan');
    Route::post('kelas/{kelas}/ajukan', [GuruKelas::class, 'ajukan'])->name('kelas.ajukan.store');
    Route::post('kelas/{kelas}/generate-pertemuan', [GuruKelas::class, 'generatePertemuan'])->name('kelas.generate-pertemuan');
    Route::post('kelas/{kelas}/saran-materi', [GuruKelas::class, 'saranMateri'])->name('kelas.saran-materi');
    Route::resource('kelas', GuruKelas::class)->parameters(['kelas' => 'kelas'])->except(['create', 'store']);
    Route::resource('kelas.pertemuan', PertemuanController::class)->parameters(['kelas' => 'kelas'])->except(['index'])->shallow();
    Route::get('pertemuan/{pertemuan}/kehadiran', [GuruKehadiran::class, 'edit'])->name('kehadiran.edit');
    Route::put('pertemuan/{pertemuan}/kehadiran', [GuruKehadiran::class, 'update'])->name('kehadiran.update');
    Route::post('pertemuan/{pertemuan}/buka', [PertemuanController::class, 'buka'])->name('pertemuan.buka');
    Route::post('pertemuan/{pertemuan}/kunci', [PertemuanController::class, 'kunci'])->name('pertemuan.kunci');
    Route::resource('materi', MateriController::class)->except(['index']);
    Route::resource('tugas', TugasController::class)->parameters(['tugas' => 'tugas']);
    Route::post('tugas/generate', [TugasController::class, 'generate'])->name('tugas.generate');
    Route::get('tugas/generate-status', [TugasController::class, 'generateStatus'])->name('tugas.generate-status');
    Route::post('tugas/{tugas}/penilaian', [TugasController::class, 'nilaiSubmit'])->name('tugas.nilai');
    Route::post('tugas/{tugas}/periksa-ai', [TugasController::class, 'periksaAI'])->name('tugas.periksa-ai');
    Route::get('tugas/{tugas}/penilaian', [TugasController::class, 'penilaian'])->name('tugas.penilaian');
    Route::resource('kuis', KuisController::class)->parameters(['kuis' => 'kuis'])->except(['show']);
    Route::post('kuis/generate', [KuisController::class, 'generate'])->name('kuis.generate');
    Route::get('kuis/generate-status', [KuisController::class, 'generateStatus'])->name('kuis.generate-status');
    Route::get('prediksi', [PrediksiController::class, 'index'])->name('prediksi');
    Route::get('prediksi/show', [PrediksiController::class, 'show'])->name('prediksi.show');


    Route::post('prediksi/run', [PrediksiController::class, 'run'])->name('prediksi.run');
    Route::post('prediksi/latih-ulang', [PrediksiController::class, 'retrain'])->name('prediksi.retrain');
    Route::post('prediksi/remedial/{siswa}', [PrediksiController::class, 'remedial'])->name('prediksi.remedial');
    Route::get('nilai', [GuruNilai::class, 'index'])->name('nilai');
    Route::get('nilai/{siswa}', [GuruNilai::class, 'show'])->name('nilai.show');
    Route::get('nilai/{siswa}/pdf', [GuruNilai::class, 'pdf'])->name('nilai.pdf');
    Route::get('fitur-siswa', [FiturSiswaController::class, 'index'])->name('fitur-siswa');
    Route::post('fitur-siswa/{siswa}', [FiturSiswaController::class, 'update'])->name('fitur-siswa.update');
    Route::get('remedial', [GuruRemedial::class, 'index'])->name('remedial.index');
    Route::get('remedial/{remedial}', [GuruRemedial::class, 'show'])->name('remedial.show');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [NotifikasiController::class, 'readAll'])->name('notifikasi.read-all');
});

Route::prefix('pelajar')->name('pelajar.')->middleware(['auth', 'role:pelajar'])->group(function () {
    Route::get('/', [SiswaDashboard::class, 'index'])->name('dashboard');
    Route::get('kelas/available', [SiswaKelas::class, 'available'])->name('kelas.available');
    Route::post('kelas/{kelas}/enroll', [SiswaKelas::class, 'enroll'])->name('kelas.enroll');
    Route::post('kelas/{kelas}/unenroll', [SiswaKelas::class, 'unenroll'])->name('kelas.unenroll');
    Route::resource('kelas', SiswaKelas::class)->parameters(['kelas' => 'kelas'])->only(['index', 'show']);
    Route::get('pertemuan/{pertemuan}', [SiswaPertemuan::class, 'show'])->name('pertemuan.show');
    Route::get('materi/{materi}', [SiswaMateri::class, 'show'])->name('materi.show');
    Route::get('materi/{materi}/download', [SiswaMateri::class, 'download'])->name('materi.download');
    Route::get('materi/{materi}/preview', [SiswaMateri::class, 'preview'])->name('materi.preview');
    Route::resource('tugas', SiswaTugas::class)->parameters(['tugas' => 'tugas'])->only(['index', 'show']);
    Route::post('tugas/{tugas}/mulai', [SiswaTugas::class, 'mulai'])->name('tugas.mulai');
    Route::post('tugas/{tugas}/submit', [SiswaTugas::class, 'submit'])->name('tugas.submit');
    Route::resource('kuis', SiswaKuis::class)->parameters(['kuis' => 'kuis'])->only(['index', 'show']);
    Route::post('kuis/{kuis}/submit', [SiswaKuis::class, 'submit'])->name('kuis.submit');
    Route::get('kuis/{kuis}/hasil', [SiswaKuis::class, 'hasil'])->name('kuis.hasil');
    Route::get('nilai', [SiswaNilai::class, 'index'])->name('nilai');
    Route::get('ai-tutor', [AITutorController::class, 'index'])->name('ai-tutor');
    Route::get('ai-tutor/kelas/{kelas}', [AITutorController::class, 'pertemuan'])->name('ai-tutor.pertemuan');
    Route::get('ai-tutor/kelas/{kelas}/pertemuan/{pertemuan}', [AITutorController::class, 'chat'])->name('ai-tutor.chat');
    Route::post('aktivitas/{aktivitas}/durasi', [SiswaAktivitas::class, 'durasi'])->name('aktivitas.durasi');
    Route::get('pengajuan-pendidikan', [SiswaPengajuanPerubahan::class, 'index'])->name('pengajuan-pendidikan.index');
    Route::get('pengajuan-pendidikan/ajukan', [SiswaPengajuanPerubahan::class, 'create'])->name('pengajuan-pendidikan.create');
    Route::post('pengajuan-pendidikan/ajukan', [SiswaPengajuanPerubahan::class, 'store'])->name('pengajuan-pendidikan.store');
    Route::get('pengajuan-pendidikan/{pengajuan}', [SiswaPengajuanPerubahan::class, 'show'])->name('pengajuan-pendidikan.show');
    Route::get('remedial', [SiswaRemedial::class, 'index'])->name('remedial.index');
    Route::get('remedial/{remedial}', [SiswaRemedial::class, 'show'])->name('remedial.show');
    Route::post('remedial/{remedial}/submit', [SiswaRemedial::class, 'submit'])->name('remedial.submit');
    Route::post('remedial/{remedial}/reset', [SiswaRemedial::class, 'reset'])->name('remedial.reset');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [NotifikasiController::class, 'readAll'])->name('notifikasi.read-all');
});

Route::prefix('pengelola-operator')->name('pengelola-operator.')->middleware(['auth', 'role:pengelola_operator'])->group(function () {
    Route::get('/', [PengelolaOperatorDashboard::class, 'index'])->name('dashboard');
    Route::resource('kategori-mata-pelajaran', PengelolaOperatorKategoriMapel::class)->parameters(['kategori-mata-pelajaran' => 'kategori']);
    Route::resource('operator', PengelolaOperatorManage::class)->except(['show']);
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [NotifikasiController::class, 'readAll'])->name('notifikasi.read-all');
});

Route::prefix('operator')->name('operator.')->middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/', [OperatorDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUser::class)->except(['create', 'store']);
    Route::post('users/{user}/verify', [AdminUser::class, 'verify'])->name('users.verify');
    Route::post('users/{user}/reject', [AdminUser::class, 'reject'])->name('users.reject');
    Route::get('users/{user}/berkas/{jenis}', [AdminUser::class, 'berkas'])->name('users.berkas');
    Route::resource('mata-pelajaran', OperatorMataPelajaran::class);
    Route::resource('kelas', AdminKelas::class)->parameters(['kelas' => 'kelas'])->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('pengajuan-mengajar/{kelas}', [OperatorPengajuan::class, 'show'])->name('pengajuan-mengajar.show');
    Route::post('pengajuan-mengajar/{pengajuan}/setujui', [OperatorPengajuan::class, 'setujui'])->name('pengajuan-mengajar.setujui');
    Route::post('pengajuan-mengajar/{pengajuan}/tolak', [OperatorPengajuan::class, 'tolak'])->name('pengajuan-mengajar.tolak');
    Route::get('pengajuan-mengajar/{pengajuan}/berkas', [OperatorPengajuan::class, 'berkas'])->name('pengajuan-mengajar.berkas');
    Route::get('pengajuan-mengajar/{pengajuan}/profil-berkas/{jenis}', [OperatorPengajuan::class, 'profilBerkas'])->name('pengajuan-mengajar.profil-berkas');
    Route::get('pengajuan-perubahan-pendidikan', [OperatorPengajuanPerubahan::class, 'index'])->name('pengajuan-perubahan-pendidikan.index');
    Route::get('pengajuan-perubahan-pendidikan/{pengajuan}', [OperatorPengajuanPerubahan::class, 'show'])->name('pengajuan-perubahan-pendidikan.show');
    Route::post('pengajuan-perubahan-pendidikan/{pengajuan}/setujui', [OperatorPengajuanPerubahan::class, 'setujui'])->name('pengajuan-perubahan-pendidikan.setujui');
    Route::post('pengajuan-perubahan-pendidikan/{pengajuan}/minta-revisi', [OperatorPengajuanPerubahan::class, 'mintaRevisi'])->name('pengajuan-perubahan-pendidikan.minta-revisi');
    Route::post('pengajuan-perubahan-pendidikan/{pengajuan}/tolak', [OperatorPengajuanPerubahan::class, 'tolak'])->name('pengajuan-perubahan-pendidikan.tolak');
    Route::get('documents', [AdminDocument::class, 'index'])->name('documents.index');
    Route::get('documents/create', [AdminDocument::class, 'create'])->name('documents.create');
    Route::post('documents', [AdminDocument::class, 'store'])->name('documents.store');
    Route::get('documents/{document}', [AdminDocument::class, 'show'])->name('documents.show');
    Route::delete('documents/{document}', [AdminDocument::class, 'destroy'])->name('documents.destroy');
    Route::post('documents/{document}/reprocess', [AdminDocument::class, 'reprocess'])->name('documents.reprocess');
    Route::get('berita', [BeritaController::class, 'index'])->name('berita.index');
    Route::get('berita/create', [BeritaController::class, 'create'])->name('berita.create');
    Route::post('berita', [BeritaController::class, 'store'])->name('berita.store');
    Route::get('berita/{berita}/edit', [BeritaController::class, 'edit'])->name('berita.edit');
    Route::put('berita/{berita}', [BeritaController::class, 'update'])->name('berita.update');
    Route::delete('berita/{berita}', [BeritaController::class, 'destroy'])->name('berita.destroy');
    Route::get('import', [OperatorImport::class, 'index'])->name('import');
    Route::get('import/template', [OperatorImport::class, 'downloadTemplate'])->name('import.template');
    Route::post('import/users', [OperatorImport::class, 'importUsers'])->name('import.users');
    Route::post('import/kelas', [OperatorImport::class, 'importKelas'])->name('import.kelas');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [NotifikasiController::class, 'readAll'])->name('notifikasi.read-all');
});

