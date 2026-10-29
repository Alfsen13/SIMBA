<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
      
    public function index(Request $request): View
    {
        $auth = auth()->user();
        $userQuery = User::query()->with('aplikasiPengajar');

        if ($auth->role === 'operator' && $auth->kategori_mapel) {
            $userQuery->whereIn('role', ['pengajar', 'pelajar'])
                ->where(function ($q) use ($auth) {
                    // Pelajar dapat dilihat oleh semua operator (tidak dibatasi kategori).
                    $q->where('role', 'pelajar')
                        ->orWhereHas('aplikasiPengajar', function ($sub) use ($auth) {
                            $sub->where('kategori_mapel', $auth->kategori_mapel);
                        });
                });
        }

        if ($request->filled('status')) {
            $userQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $userQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $userQuery->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

      
    public function show(User $user): View
    {
        $user->loadMissing([
            'profilPendidikan.jalur',
            'profilPendidikan.jenjang',
            'profilPendidikan.tingkat',
            'aplikasiPengajar',
        ]);

        return view('admin.users.show', compact('user'));
    }

     /**
      * Unduh berkas dari pendaftaran (CV/ijazah calon pengajar). Disajikan
      * lewat operator supaya path di disk public tidak perlu dibuka langsung.
      */
    public function berkas(User $user, string $jenis)
    {
        $path = match ($jenis) {
            'cv' => $user->aplikasiPengajar?->cv_path,
            'ijazah' => $user->aplikasiPengajar?->ijazah_path,
            default => null,
        };

        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

      /**
       * Tampilkan dokumen di halaman (viewer mode).
       */
    public function viewer(User $user, string $jenis, ?string $filename = null): View
    {
        $path = match ($jenis) {
            'cv' => $user->aplikasiPengajar?->cv_path,
            'ijazah' => $user->aplikasiPengajar?->ijazah_path,
            default => null,
        };

        $filename = $filename ? urldecode($filename) : basename($path);
        
        abort_unless($path && $filename && Storage::disk('public')->exists($filename), 404);

        return view('admin.users.viewer', [
            'user' => $user,
            'jenis' => $jenis,
            'filename' => basename($filename),
            'filePath' => $filename,
            'fileUrl' => route('operator.users.berkas', ['user' => $user, 'jenis' => $jenis]),
        ]);
    }

      
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

      
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update($validated);

        return redirect()->route('operator.users.index')->with('success', 'User berhasil diupdate.');
    }

      
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $auth = auth()->user();
        if ($auth->role === 'operator' && $auth->kategori_mapel) {
            $kategori = $user->aplikasiPengajar?->kategori_mapel;
            abort_unless($kategori && $kategori === $auth->kategori_mapel, 403, 'Tidak berwenang menghapus akun pada kategori ini.');
        }

        DB::transaction(function () use ($user) {
            $user->loadMissing(['aplikasiPengajar', 'profilPendidikan']);

            if ($user->aplikasiPengajar) {
                if ($user->aplikasiPengajar->cv_path && Storage::disk('public')->exists($user->aplikasiPengajar->cv_path)) {
                    Storage::disk('public')->delete($user->aplikasiPengajar->cv_path);
                }
                if ($user->aplikasiPengajar->ijazah_path && Storage::disk('public')->exists($user->aplikasiPengajar->ijazah_path)) {
                    Storage::disk('public')->delete($user->aplikasiPengajar->ijazah_path);
                }
                $user->aplikasiPengajar->delete();
            }

            if ($user->profilPendidikan) {
                $user->profilPendidikan->delete();
            }

            $user->kelasDiikuti()->detach();
            $user->kelasMenunggu()->detach();
            $user->semuaKelasDiikuti()->detach();

            $user->chatLog()->delete();
            $user->chatSummary()->delete();
            $user->sesiSummary()->delete();
            $user->notifikasi()->delete();
            $user->prediksiAi()->delete();
            $user->aktivitasLog()->delete();
            $user->pengumpulanTugas()->delete();

            if (method_exists($user, 'fiturHybridSiswa') && $user->fiturHybridSiswa) {
                $user->fiturHybridSiswa->delete();
            }

            if ($user->role === 'pengajar') {
                $user->kelas()->update(['guru_id' => null]);
            }

            $user->delete();
        });

        return redirect()->route('operator.users.index')->with('success', 'User berhasil dihapus beserta data terkait.');
    }

      
    public function verify(User $user): RedirectResponse
    {
        $auth = auth()->user();

        // Operator hanya dibatasi kategori untuk verifikasi pengajar/operator dalam kategori yang sama.
        // Pelajar dapat diverifikasi oleh semua operator.
        if ($auth->role === 'operator' && $auth->kategori_mapel && $user->role !== 'pelajar') {
            $kategori = $user->aplikasiPengajar?->kategori_mapel;
            abort_unless($kategori && $kategori === $auth->kategori_mapel, 403, 'Tidak berwenang memverifikasi akun pada kategori ini.');
        }

        $user->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'User berhasil diverifikasi.');
    }

      
    public function reject(User $user): RedirectResponse
    {
        $auth = auth()->user();

        // Operator hanya dibatasi kategori untuk penolakan pengajar/operator dalam kategori yang sama.
        // Pelajar dapat ditolak oleh semua operator.
        if ($auth->role === 'operator' && $auth->kategori_mapel && $user->role !== 'pelajar') {
            $kategori = $user->aplikasiPengajar?->kategori_mapel;
            abort_unless($kategori && $kategori === $auth->kategori_mapel, 403, 'Tidak berwenang menolak akun pada kategori ini.');
        }

        $user->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'User ditolak.');
    }
}
