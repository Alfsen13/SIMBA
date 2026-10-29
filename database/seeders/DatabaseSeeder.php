<?php

namespace Database\Seeders;

use App\Models\AplikasiPengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengajarKompetensi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SymbolSeeder::class);
        $this->call(MasterPendidikanSeeder::class);
        $this->call(MataPelajaranSeeder::class);
        $this->call(KelasAwalSeeder::class);

        $this->createPengelolaOperator();
        $this->createOperatorsPerKategori();
        $this->assignKelasKeOperator();
    }

    private function createPengelolaOperator(): void
    {
        User::firstOrCreate(
            ['email' => 'pengelola_operator@simba.test'],
            [
                'name' => 'Pengelola Operator',
                'password' => Hash::make('password'),
                'role' => 'pengelola_operator',
                'status' => 'verified',
                'verified_at' => now(),
            ]
        );
    }

    private function createOperatorsPerKategori(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@simba.test'],
            [
                'name' => 'Admin SIMBA',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'status' => 'verified',
                'verified_at' => now(),
            ]
        );

        $mapelByKategori = MataPelajaran::query()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->orderBy('kode')
            ->get()
            ->groupBy('kategori');

        foreach ($mapelByKategori as $kategori => $mapels) {
            $slug = Str::slug($kategori);
            $email = "operator.{$slug}@simba.test";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Operator {$kategori}",
                    'password' => Hash::make('password'),
                    'role' => 'operator',
                    'status' => 'verified',
                    'verified_at' => now(),
                    'kategori_mapel' => $kategori,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $user->refresh();
            }

            AplikasiPengajar::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'pendidikan_tertinggi' => 'S1 Pendidikan',
                    'pengalaman_mengajar' => '5 tahun',
                    'cv_path' => "aplikasi/cv/{$slug}.pdf",
                    'ijazah_path' => "aplikasi/ijazah/{$slug}.pdf",
                    'kategori_mapel' => $kategori,
                    'mata_pelajaran' => $mapels->pluck('kode')->toArray(),
                    'jenjang_dan_tingkat' => 'SMA/SMK',
                    'status_aplikasi' => 'approved',
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                    'catatan_operator' => 'Disetujui otomatis via seeder.',
                ]
            );

            foreach ($mapels as $mapel) {
                PengajarKompetensi::firstOrCreate(
                    ['user_id' => $user->id, 'mata_pelajaran_id' => $mapel->id],
                    [
                        'status' => 'disetujui',
                        'disetujui_oleh' => $admin->id,
                        'disetujui_pada' => now(),
                        'catatan' => 'Kompetensi ditetapkan via seeder.',
                    ]
                );
            }
        }
    }

    private function assignKelasKeOperator(): void
    {
        // Ambil semua operator yang memiliki kategori_mapel, indekskan per kategori.
        $operatorsByKategori = User::where('role', 'operator')
            ->whereNotNull('kategori_mapel')
            ->get()
            ->groupBy('kategori_mapel');

        // Kelas yang belum punya guru, di-assign ke operator yang kategori_mapel-nya
        // cocok dengan kategori mata pelajarannya.
        $kelasTanpaGuru = Kelas::whereNull('guru_id')->get();

        $dipakai = [];
        foreach ($kelasTanpaGuru as $kelas) {
            if (!$kelas->mata_pelajaran_id) {
                continue;
            }

            $mapel = MataPelajaran::find($kelas->mata_pelajaran_id);
            if (!$mapel || !$mapel->kategori) {
                continue;
            }

            $operator = $operatorsByKategori->get($mapel->kategori)?->first();
            if (!$operator) {
                continue;
            }

            $kelas->update(['guru_id' => $operator->id]);
            $dipakai[$operator->id] = true;
        }
    }
}
