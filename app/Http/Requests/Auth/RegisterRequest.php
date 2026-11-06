<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\KategoriMataPelajaran;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\MataPelajaran;
use App\Models\TingkatPendidikan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'jalur_id' => ['nullable', 'integer', Rule::exists('jalur_pendidikan', 'id')->where('aktif', true), 'prohibited_unless:role,pelajar'],
            'jenjang_id' => ['nullable', 'integer', Rule::exists('jenjang_pendidikan', 'id')->where('aktif', true), 'prohibited_unless:role,pelajar'],
            'tingkat_id' => ['nullable', 'integer', Rule::exists('tingkat_pendidikan', 'id')->where('aktif', true), 'prohibited_unless:role,pelajar'],
            'institusi' => ['nullable', 'string', 'max:255', 'prohibited_unless:role,pelajar'],
            'program' => ['nullable', 'string', 'max:255', 'prohibited_unless:role,pelajar'],
            'minat' => ['nullable', 'string', 'max:500', 'prohibited_unless:role,pelajar'],
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
            $rules['jalur_id'] = ['required', 'integer', Rule::exists('jalur_pendidikan', 'id')->where('aktif', true)];
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('role') !== 'pelajar' || $validator->errors()->has('jalur_id')) {
                    return;
                }

                $jalur = JalurPendidikan::query()
                    ->whereKey($this->input('jalur_id'))
                    ->where('aktif', true)
                    ->first(['id', 'kode']);

                if (! $jalur) {
                    return;
                }

                $butuhJenjangDanTingkat = in_array($jalur->kode, ['formal', 'nonformal'], true);
                $jenjangId = $this->input('jenjang_id');
                $tingkatId = $this->input('tingkat_id');

                if ($butuhJenjangDanTingkat && blank($jenjangId)) {
                    $validator->errors()->add('jenjang_id', 'Jenjang pendidikan wajib dipilih untuk jalur ini.');
                }

                if ($butuhJenjangDanTingkat && blank($tingkatId)) {
                    $validator->errors()->add('tingkat_id', 'Tingkat pendidikan wajib dipilih untuk jalur ini.');
                }

                if (filled($jenjangId) && ! $validator->errors()->has('jenjang_id')
                    && ! JenjangPendidikan::query()
                        ->whereKey($jenjangId)
                        ->where('jalur_id', $jalur->id)
                        ->where('aktif', true)
                        ->exists()) {
                    $validator->errors()->add('jenjang_id', 'Jenjang tujuan tidak sesuai dengan jalur pendidikan yang dipilih.');
                }

                if (filled($tingkatId) && ! $validator->errors()->has('tingkat_id')) {
                    if (blank($jenjangId)) {
                        $validator->errors()->add('jenjang_id', 'Jenjang pendidikan wajib dipilih sebelum memilih tingkat.');
                    } elseif (! JenjangPendidikan::query()
                        ->whereKey($jenjangId)
                        ->where('jalur_id', $jalur->id)
                        ->where('aktif', true)
                        ->exists()) {
                        return;
                    } elseif (! TingkatPendidikan::query()
                        ->whereKey($tingkatId)
                        ->where('jenjang_id', $jenjangId)
                        ->where('aktif', true)
                        ->exists()) {
                        $validator->errors()->add('tingkat_id', 'Tingkat tujuan tidak sesuai dengan jenjang yang dipilih.');
                    }
                }
            },
            function (Validator $validator): void {
                if ($this->input('role') !== 'pengajar'
                    || $validator->errors()->has('kategori_mapel')
                    || $validator->errors()->has('mata_pelajaran')) {
                    return;
                }

                $kategori = Str::lower(trim((string) $this->input('kategori_mapel')));
                $mapel = collect($this->input('mata_pelajaran', []))
                    ->filter(fn ($nama, $index) => is_string($nama)
                        && ! $validator->errors()->has("mata_pelajaran.{$index}"))
                    ->map(fn (string $nama) => trim($nama))
                    ->filter()
                    ->unique();

                if ($mapel->isEmpty()) {
                    return;
                }

                $namaTernormalisasi = $mapel
                    ->map(fn (string $nama) => Str::lower($nama))
                    ->unique()
                    ->values();

                $mapelKatalog = MataPelajaran::query()
                    ->whereIn(DB::raw('LOWER(TRIM(nama))'), $namaTernormalisasi)
                    ->get(['nama', 'kategori', 'aktif'])
                    ->groupBy(fn (MataPelajaran $item) => Str::lower(trim($item->nama)));

                foreach ($mapel as $index => $nama) {
                    $kandidat = $mapelKatalog->get(Str::lower($nama));

                    if ($kandidat && ! $kandidat->contains(
                        fn (MataPelajaran $item) => $item->aktif
                            && Str::lower(trim((string) $item->kategori)) === $kategori
                    )) {
                        $validator->errors()->add(
                            "mata_pelajaran.{$index}",
                            'Mata pelajaran katalog harus aktif dan sesuai dengan kategori yang dipilih.'
                        );
                    }
                }
            },
        ];
    }

    /** Kategori kelompok mapel yang tersedia di master (untuk Rule::in). */
    private static function kategoriMapelTersedia(): array
    {
        return KategoriMataPelajaran::query()
            ->whereHas('mataPelajaran', fn ($query) => $query->where('aktif', true))
            ->distinct()
            ->pluck('nama')
            ->all();
    }
}
