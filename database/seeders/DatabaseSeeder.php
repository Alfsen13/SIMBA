<?php

namespace Database\Seeders;

use App\Models\KategoriMataPelajaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('seeding.operator_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('SEED_OPERATOR_PASSWORD harus diisi sebelum menjalankan seeder.');
        }

        $this->call([
            SymbolSeeder::class,
            MasterPendidikanSeeder::class,
            MataPelajaranSeeder::class,
            KelasAwalSeeder::class,
        ]);

        $this->buatAkun('pengelola_operator@simba.test', 'Pengelola Operator', 'pengelola_operator', null, $password);

        foreach (KategoriMataPelajaran::orderBy('nama')->pluck('nama') as $kategori) {
            $this->buatAkun(
                'operator.'.Str::slug($kategori).'@simba.test',
                "Operator {$kategori}",
                'operator',
                $kategori,
                $password,
            );
        }
    }

    private function buatAkun(string $email, string $nama, string $role, ?string $kategori, string $password): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->role !== $role || $user->kategori_mapel !== $kategori) {
                throw new RuntimeException("Akun {$email} sudah digunakan untuk peran atau kategori lain.");
            }

            return;
        }

        User::create([
            'email' => $email,
            'name' => $nama,
            'password' => Hash::make($password),
            'role' => $role,
            'status' => 'verified',
            'verified_at' => now(),
            'kategori_mapel' => $kategori,
        ]);
    }
}
