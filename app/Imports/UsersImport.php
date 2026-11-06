<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class UsersImport
{
    private const array COLUMNS = ['name', 'email', 'role', 'nip', 'nisn', 'password'];

    public int $imported = 0;

    public array $errors = [];

    private int $rowNumber = 1;

    public function import(string $filePath, string $extension): void
    {
        $this->imported = 0;
        $this->errors = [];
        $this->rowNumber = 1;

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
                    $headers = array_map(
                        fn ($cell) => strtolower(trim(ltrim((string) $cell, "\xEF\xBB\xBF"))),
                        array_map(fn ($cell) => $cell->getValue(), $row->cells)
                    );

                    if ($headers !== self::COLUMNS) {
                        $this->errors[] = 'Baris 1: urutan kolom harus '.implode(',', self::COLUMNS).'.';
                        break;
                    }

                    continue;
                }

                $this->rowNumber++;
                $cells = [];
                foreach ($row->cells as $cell) {
                    $cells[] = $cell->getValue();
                }

                if (count(array_filter($cells, fn ($cell) => $cell !== null && $cell !== '')) === 0) {
                    continue;
                }

                $this->processRow($cells, $this->rowNumber);
            }
        }

        $reader->close();
    }

    protected function processRow(array $cells, int $rowNum): void
    {
        if (count($cells) > 6) {
            $this->errors[] = 'Baris '.$rowNum.': jumlah kolom harus tepat 6.';

            return;
        }

        $row = array_combine(self::COLUMNS, array_pad($cells, 6, null));
        $row = array_map(
            fn ($value) => $value === null || (is_string($value) && trim($value) === '')
                ? null
                : (is_scalar($value) ? trim((string) $value) : $value),
            $row
        );

        $validator = Validator::make($row, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:pengajar,pelajar'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip'],
            'nisn' => ['required_if:role,pelajar', 'nullable', 'string', 'max:50', 'unique:users,nisn'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->errors[] = 'Baris '.$rowNum.': '.implode(' ', $validator->errors()->all());

            return;
        }

        $validated = $validator->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'verified';

        User::create($validated);
        $this->imported++;
    }
}
