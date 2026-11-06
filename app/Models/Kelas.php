<?php

namespace App\Models;

use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
     
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas', 'kode_kelas', 'deskripsi', 'kategori', 'guru_id',
        'jalur_id', 'jenjang_id', 'tingkat_id', 'mata_pelajaran_id',
        'kapasitas', 'status', 'aturan_kelayakan', 'periode',
    ];

     
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /** Pengajuan pengajar mengisi kelas ini (bahan rencana ajar untuk tinjauan operator). */
    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanPengajarKelas::class);
    }

     
    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kelas_siswa', 'kelas_id', 'siswa_id')
            ->wherePivot('status', 'active');
    }

     
    public function siswaMenunggu(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kelas_siswa', 'kelas_id', 'siswa_id')
            ->wherePivot('status', 'pending')
            ->withPivot(['status', 'approved_by', 'approved_at']);
    }

     
    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class);
    }

     
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class);
    }

     
    public function pertemuan(): HasMany
    {
        return $this->hasMany(Pertemuan::class);
    }

     
    public function kuis(): HasMany
    {
        return $this->hasMany(Kuis::class);
    }
}
