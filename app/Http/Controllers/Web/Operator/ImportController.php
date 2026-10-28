<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\ImportRequest;
use App\Imports\KelasImport;
use App\Imports\UsersImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ImportController extends Controller
{
      
    public function index(): View
    {
        return view('operator.import.index');
    }

      
    public function downloadTemplate(Request $request = null): Response
    {
        $headers = [
            'nama_kelas',
            'mata_pelajaran',
            'kategori',
            'jalur',
            'jenjang',
            'tingkat',
            'kapasitas',
            'periode',
            'status',
            'aturan_kelayakan',
            'deskripsi',
        ];
        $example = [
            'Matematika Kelas 4 SD',
            'Matematika',
            'Matematika & Ilmu Alam',
            'Formal',
            'SD',
            'Kelas 4',
            '30',
            '2026/2027 Ganjil',
            'dibuka',
            'sesuai_profil',
            'Pembelajaran matematika untuk siswa SD kelas 4.',
        ];

        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $headers, ',');
        fputcsv($output, $example, ',');
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-kelas.csv"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

      
    public function importUsers(ImportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $file = $validated['file'];
        $extension = $file->getClientOriginalExtension();

        $importer = new UsersImport;
        $importer->import($file->getRealPath(), $extension);

        $redirectResponse = redirect()->route('operator.import')
            ->with('success', $importer->imported.' pengguna berhasil diimport.');

        if ($importer->errors) {
            $redirectResponse->with('import_errors', $importer->errors);
        }

        return $redirectResponse;
    }

      
    public function importKelas(ImportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $file = $validated['file'];
        $extension = $file->getClientOriginalExtension();

        $importer = new KelasImport;
        $importer->import($file->getRealPath(), $extension);

        $redirectResponse = redirect()->route('operator.kelas.index')
            ->with('success', $importer->imported.' kelas berhasil diimport.');

        if ($importer->errors) {
            $redirectResponse->with('import_errors', $importer->errors);
        }

        return $redirectResponse;
    }
}

