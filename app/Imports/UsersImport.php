<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class UsersImport
{
    public int $imported = 0;

    public array $errors = [];

    public function import(string $filePath, string $extension): void
    {
        $reader = match (strtolower($extension)) {
            'xlsx' => new XlsxReader,
            default => new CsvReader,
        };

        $reader->open($filePath);

        $firstSheet = true;

        foreach ($reader->getSheetIterator() as $sheet) {
            if (! $firstSheet) {
                break;
            }
            $firstSheet = false;

            $isHeader = true;

            foreach ($sheet->getRowIterator() as $row) {
                if ($isHeader) {
                    $isHeader = false;

                    continue;
                }

                $cells = [];
                foreach ($row->cells as $cell) {
                    $cells[] = $cell->getValue();
                }

                $this->processRow($cells);
            }
        }

        $reader->close();
    }

    protected function processRow(array $cells): void
    {
        $rowNum = $this->imported + count($this->errors) + 2;

        try {
            $row = array_combine(
                ['name', 'email', 'password', 'role', 'nip', 'nisn'],
                array_pad($cells, 6, null)
            );

            User::create([
                'name' => $row['name'] ?? '',
                'email' => $row['email'] ?? '',
                'password' => Hash::make($row['password'] ?? 'password'),
                'role' => $row['role'] ?? 'pelajar',
                'status' => 'verified',
                'nip' => $row['nip'] ?: null,
                'nisn' => $row['nisn'] ?: null,
            ]);

            $this->imported++;
        } catch (\Exception $e) {
            $this->errors[] = 'Baris '.$rowNum.': '.$e->getMessage();
        }
    }
}
