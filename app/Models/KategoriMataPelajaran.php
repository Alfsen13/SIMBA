<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriMataPelajaran extends Model
{
    protected $table = 'kategori_mata_pelajaran';

    protected $fillable = ['nama'];

    public function mataPelajaran(): HasMany
    {
        return $this->hasMany(MataPelajaran::class, 'kategori', 'nama');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'kategori', 'nama');
    }

    public function operators(): HasMany
    {
        return $this->hasMany(User::class, 'kategori_mapel', 'nama')
            ->where('role', 'operator');
    }

    public function aplikasiPengajar(): HasMany
    {
        return $this->hasMany(AplikasiPengajar::class, 'kategori_mapel', 'nama');
    }
}
