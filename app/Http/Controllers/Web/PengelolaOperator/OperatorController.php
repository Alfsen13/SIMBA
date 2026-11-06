<?php

namespace App\Http\Controllers\Web\PengelolaOperator;

use App\Http\Controllers\Controller;
use App\Models\KategoriMataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function index(): View
    {
        $operators = User::where('role', 'operator')->latest()->paginate(15);

        return view('pengelola-operator.operator.index', compact('operators'));
    }

    public function create(): View
    {
        $kategoriList = KategoriMataPelajaran::query()
            ->orderBy('nama')
            ->pluck('nama', 'nama');

        return view('pengelola-operator.operator.create', compact('kategoriList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'kategori_mapel' => ['required', 'string', 'max:100', Rule::exists('kategori_mata_pelajaran', 'nama')],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'operator',
            'status' => 'verified',
            'kategori_mapel' => $validated['kategori_mapel'],
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return redirect()->route('pengelola-operator.operator.index')->with('success', 'Akun operator berhasil dibuat.');
    }

    public function edit(User $operator): View
    {
        $this->authorizeOperatorTarget($operator);

        $kategoriList = KategoriMataPelajaran::query()
            ->orderBy('nama')
            ->pluck('nama', 'nama');

        return view('pengelola-operator.operator.edit', compact('operator', 'kategoriList'));
    }

    public function update(Request $request, User $operator): RedirectResponse
    {
        $this->authorizeOperatorTarget($operator);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$operator->id],
            'kategori_mapel' => ['required', 'string', 'max:100', Rule::exists('kategori_mata_pelajaran', 'nama')],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'kategori_mapel' => $validated['kategori_mapel'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $operator->update($data);

        return redirect()->route('pengelola-operator.operator.index')->with('success', 'Akun operator berhasil diperbarui.');
    }

    public function destroy(User $operator): RedirectResponse
    {
        $this->authorizeOperatorTarget($operator);

        if ($operator->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $operator->delete();

        return redirect()->route('pengelola-operator.operator.index')->with('success', 'Akun operator berhasil dihapus.');
    }

    private function authorizeOperatorTarget(User $operator): void
    {
        abort_unless($operator->role === 'operator', 403);
    }
}
