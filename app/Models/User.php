<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'nip', 'nisn', 'verified_by', 'verified_at', 'kategori_mapel'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
     
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified_at' => 'datetime',
        ];
    }

     
    public function kelasDiikuti(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa', 'siswa_id', 'kelas_id')
            ->wherePivot('status', 'active')
            ->withPivot(['status']);
    }

     
    public function kelasMenunggu(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa', 'siswa_id', 'kelas_id')
            ->wherePivot('status', 'pending')
            ->withPivot(['status', 'approved_by', 'approved_at']);
    }

     
    public function semuaKelasDiikuti(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa', 'siswa_id', 'kelas_id')
            ->withPivot(['status', 'approved_by', 'approved_at']);
    }

     
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    
    public function prediksiAi(): HasMany
    {
        return $this->hasMany(PrediksiAi::class, 'siswa_id');
    }

     
    public function aktivitasLog(): HasMany
    {
        return $this->hasMany(AktivitasLog::class, 'siswa_id');
    }

     
    public function pengumpulanTugas(): HasMany
    {
        return $this->hasMany(PengumpulanTugas::class, 'siswa_id');
    }

     
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'guru_id');
    }

     
    public function fiturHybridSiswa(): HasOne
    {
        return $this->hasOne(FiturHybridSiswa::class, 'siswa_id');
    }

    public function aiSetting(): HasOne
    {
        return $this->hasOne(UserAiSetting::class);
    }

    public function profilPendidikan(): HasOne
    {
        return $this->hasOne(ProfilPendidikan::class);
    }

    /** Aplikasi calon pengajar (berkas CV/ijazah + kompetensi yang diklaim). */
    public function aplikasiPengajar(): HasOne
    {
        return $this->hasOne(AplikasiPengajar::class);
    }

    public function pengajuanPerubahanPendidikan(): HasMany
    {
        return $this->hasMany(PengajuanPerubahanPendidikan::class);
    }

    public function isPengelolaOperator(): bool
    {
        return $this->role === 'pengelola_operator';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }
}

