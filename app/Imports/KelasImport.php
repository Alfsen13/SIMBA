<?php

namespace App\Imports;

use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class KelasImport
{
    public int $imported = 0;

    public array $errors = [];

    public function import(string $filePath, string $extension): void
    {
        $ext = strtolower($extension);
        if ($ext === 'xlsx') {
            $reader = new XlsxReader;
            $reader->open($filePath);
            $firstSheet = true;
            foreach ($reader->getSheetIterator() as $sheet) {
                if (! $firstSheet) { break; }
                $firstSheet = false;
                $this->processSheet($sheet);
            }
            $reader->close();
            return;
        }

        $reader = new CsvReader;
        $reader->open($filePath);
        $firstSheet = true;
        foreach ($reader->getSheetIterator() as $sheet) {
            if (! $firstSheet) { break; }
            $firstSheet = false;
            $this->processSheet($sheet);
        }
        $reader->close();
    }

    private function processSheet($sheet): void
    {
        $isHeader = true;
        $headers = [];

        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            foreach ($row->cells as $cell) {
                $value = $cell->getValue();
                if (is_string($value)) {
                    $value = trim(str_replace("\u{00A0}", ' ', $value));
                }
                $cells[] = $value;
            }

            if ($isHeader) {
                $headers = array_map(function ($h) {
                    $h = strtolower(trim((string) $h));
                    $h = str_replace(["\u{00A0}", ' '], [' ', ' '], $h);
                    $h = preg_replace('/\s+/', '_', $h);
                    return match ($h) {
                        'mata_pelajaran_id', 'kode_mata_pelajaran', 'mapel', 'mata_pelajaran_kode' => 'mata_pelajaran',
                        'jalur_id', 'jalur_kode' => 'jalur',
                        'jenjang_id', 'jenjang_kode' => 'jenjang',
                        'tingkat_id', 'tingkat_kode' => 'tingkat',
                        'nama_kelas' => 'nama_kelas',
                        default => $h,
                    };
                }, $cells);
                $isHeader = false;
                continue;
            }

            if (empty(array_filter($cells, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $this->processRow($headers, $cells);
        }
    }

    protected function processRow(array $headers, array $cells): void
    {
        $rowNum = $this->imported + count($this->errors) + 2;

        try {
            $row = [];
            foreach ($headers as $idx => $header) {
                $row[$header] = $cells[$idx] ?? null;
            }

            $namaKelas = trim((string) ($row['nama_kelas'] ?? ''));
            if ($namaKelas === '' || strtolower($namaKelas) === 'nama_kelas') {
                throw new \Exception('nama_kelas wajib diisi.');
            }

            $mapelId = $this->val($row['mata_pelajaran'] ?? ($row['mata_pelajaran_id'] ?? null));
            $mapel = null;
            if ($mapelId !== null) {
                if (is_numeric($mapelId)) {
                    $mapel = DB::table('mata_pelajaran')->where('id', (int) $mapelId)->first(['id', 'kategori', 'kode', 'nama']);
                } else {
                    $mapel = DB::table('mata_pelajaran')->where('kode', $mapelId)->orWhere('nama', 'like', $mapelId)->first(['id', 'kategori', 'kode', 'nama']);
                }
                if (! $mapel) {
                    throw new \Exception("mata_pelajaran '{$mapelId}' tidak ditemukan.");
                }
            }

            $jalurId = $this->cariId('jalur_pendidikan', $row['jalur'] ?? ($row['jalur_id'] ?? null));
            $jenjangId = $this->cariId('jenjang_pendidikan', $row['jenjang'] ?? ($row['jenjang_id'] ?? null));
            $tingkatId = $this->cariId('tingkat_pendidikan', $row['tingkat'] ?? ($row['tingkat_id'] ?? null));

            $status = $this->val($row['status'] ?? null) ?? 'dibuka';
            $aturan = $this->val($row['aturan_kelayakan'] ?? null) ?? 'sesuai_profil';
            $kapasitas = $this->val($row['kapasitas'] ?? null);
            $periode = $this->val($row['periode'] ?? null);
            $kategori = $this->val($row['kategori'] ?? null) ?? ($mapel->kategori ?? null);
            $deskripsi = $this->val($row['deskripsi'] ?? null);

            Kelas::create([
                'nama_kelas' => $namaKelas,
                'deskripsi' => $deskripsi,
                'kategori' => $kategori,
                'kode_kelas' => strtoupper(Str::random(8)),
                'guru_id' => null,
                'mata_pelajaran_id' => $mapel->id ?? null,
                'jalur_id' => $jalurId,
                'jenjang_id' => $jenjangId,
                'tingkat_id' => $tingkatId,
                'kapasitas' => (is_numeric($kapasitas) && $kapasitas !== '') ? (int) $kapasitas : null,
                'status' => in_array($status, ['draft','dibuka','ditutup','arsip'], true) ? $status : 'dibuka',
                'aturan_kelayakan' => in_array($aturan, ['sesuai_profil','sesuai_jenjang','terbuka','khusus'], true) ? $aturan : 'sesuai_profil',
                'periode' => $periode,
            ]);

            $this->imported++;
        } catch (\Exception $e) {
            $this->errors[] = 'Baris '.$rowNum.': '.$e->getMessage();
        }
    }

    private function cariId(string $tabel, $nilai): ?int
    {
        $nilai = $this->val($nilai);
        if ($nilai === null) {
            return null;
        }
        if (is_numeric($nilai)) {
            $cek = DB::table($tabel)->where('id', (int) $nilai)->exists();
            if ($cek) { return (int) $nilai; }
        }

        if ($tabel === 'jalur_pendidikan') {
            $id = DB::table($tabel)->whereRaw('LOWER(kode)=?', [strtolower($nilai)])->orWhere('nama', $nilai)->value('id');
        } elseif ($tabel === 'jenjang_pendidikan') {
            $id = DB::table($tabel)->whereRaw('LOWER(kode)=?', [strtolower($nilai)])->orWhere('nama', $nilai)->value('id');
        } elseif ($tabel === 'tingkat_pendidikan') {
            $id = DB::table($tabel)->where('kode', Str::slug($nilai, '_'))->orWhere('nama', $nilai)->orWhereRaw('LOWER(nama)=?', [strtolower($nilai)])->value('id');
        } else {
            $id = DB::table($tabel)->whereRaw('LOWER(kode)=?', [strtolower($nilai)])->orWhere('nama', $nilai)->value('id');
        }

        if ($id === null) {
            throw new \Exception("'{$nilai}' tidak ditemukan di tabel {$tabel}.");
        }

        return (int) $id;
    }

    private function val($v): ?string
    {
        if ($v === null) {
            return null;
        }
        if (is_int($v) || is_float($v)) {
            $s = (string) $v;
            if ($s === '') { return null; }
            return $s;
        }
        $s = trim((string) $v);
        if ($s === '' || strtolower($s) === 'null' || strtolower($s) === '#n/a') {
            return null;
        }

        return $s;
    }
}
