<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\MataPelajaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:pengajar,pelajar'],
            'nip' => ['nullable', 'string', 'unique:users'],
            'nisn' => ['nullable', 'string', 'unique:users'],
            'jalur_id' => ['nullable', 'integer', 'exists:jalur_pendidikan,id'],
            'jenjang_id' => ['nullable', 'integer', 'exists:jenjang_pendidikan,id'],
            'tingkat_id' => ['nullable', 'integer', 'exists:tingkat_pendidikan,id'],
            'institusi' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'minat' => ['nullable', 'string', 'max:500'],
            'pendidikan_tertinggi' => ['nullable', 'string', 'max:255'],
            'pengalaman_mengajar' => ['nullable', 'string', 'max:2000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'ijazah' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'kategori_mapel' => ['nullable', 'string', 'max:100', Rule::in(self::kategoriMapelTersedia())],
            'mata_pelajaran' => ['nullable', 'array'],
            'mata_pelajaran.*' => ['string', 'max:255'],
            'jenjang_dan_tingkat' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->input('role') === 'pengajar') {
            $rules['nip'] = ['nullable', 'string', 'unique:users'];
            $rules['kategori_mapel'] = ['required', 'string', 'max:100', Rule::in(self::kategoriMapelTersedia())];
        } elseif ($this->input('role') === 'pelajar') {
            $rules['nisn'] = ['required', 'string', 'unique:users'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'NIP wajib diisi untuk pendaftar pengajar.',
            'nisn.required' => 'NISN wajib diisi untuk pendaftar pelajar.',
            'kategori_mapel.required' => 'Kategori mata pelajaran wajib dipilih untuk pendaftar pengajar.',
        ];
    }

    /** Kategori kelompok mapel yang tersedia di master (untuk Rule::in). */
    private static function kategoriMapelTersedia(): array
    {
        return MataPelajaran::query()
            ->where('aktif', true)
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->all();
    }
}

