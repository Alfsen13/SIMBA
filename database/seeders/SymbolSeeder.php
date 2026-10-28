<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SymbolSeeder extends Seeder
{
    /**
     * Kolom yang di-upsert dari berkas JSON.
     *
     * @var list<string>
     */
    private const COLUMNS = [
        'id',
        'domain',
        'category',
        'subcategory',
        'symbol',
        'latex',
        'name',
        'definition',
        'meaning',
        'formula',
        'variables',
        'unit',
        'example',
        'related_symbols',
        'related_concepts',
        'common_misunderstanding',
        'keywords',
        'retrieval_text',
    ];

    /**
     * Field yang disimpan sebagai JSON di kolom text.
     *
     * @var list<string>
     */
    private const JSON_FIELDS = [
        'variables',
        'related_symbols',
        'related_concepts',
        'keywords',
    ];

    /**
     * Jumlah baris per upsert agar query tidak terlalu besar.
     */
    private const CHUNK = 100;

    public function run(): void
    {
        $records = $this->collectRecords();

        if ($records === []) {
            $this->command->warn('SymbolSeeder: tidak ada record simbol ditemukan. Lewati.');

            return;
        }

        $inserted = 0;
        $updated = 0;

        $existingIds = collect(DB::table('symbols')->pluck('id'))->flip();

        foreach (array_chunk($records, self::CHUNK) as $chunk) {
            $now = now();

            foreach ($chunk as $record) {
                $existingIds->has($record['id']) ? $updated++ : $inserted++;
            }

            DB::table('symbols')->upsert(
                array_map(fn (array $row) => $row + [
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk),
                ['id'],
                array_values(array_diff(self::COLUMNS, ['id', 'created_at']))
            );
        }

        $this->command->info(sprintf(
            'SymbolSeeder: %d record dari %d berkas (inserted=%d, updated=%d).',
            count($records),
            count($this->sourcePaths()),
            $inserted,
            $updated
        ));
    }

    /**
     * Semua berkas sumber simbol. Berkas legacy diletakkan lebih dulu agar
     * isinya ditimpa oleh berkas domain yang lebih baru.
     *
     * @return list<string>
     */
    private function sourcePaths(): array
    {
        $paths = File::isDirectory(database_path('data/symbols'))
            ? File::glob(database_path('data/symbols/*.json'))
            : [];

        sort($paths);

        $legacy = database_path('data/symbols.json');

        return File::exists($legacy) ? array_merge([$legacy], $paths) : $paths;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectRecords(): array
    {
        $records = [];
        $seen = [];

        foreach ($this->sourcePaths() as $path) {
            $raw = File::get($path);

            if (str_starts_with($raw, "\xEF\xBB\xBF")) {
                $raw = substr($raw, 3);
            }

            $decoded = json_decode($raw, true);

            if (! is_array($decoded) || ! isset($decoded['records']) || ! is_array($decoded['records'])) {
                $this->command->warn(sprintf(
                    'SymbolSeeder: lewati %s (JSON tidak valid atau tanpa key "records").',
                    File::basename($path)
                ));

                continue;
            }

            $added = 0;

            foreach ($decoded['records'] as $record) {
                if (! is_array($record) || empty($record['id'])) {
                    continue;
                }

                if (isset($seen[$record['id']])) {
                    $this->command->warn(sprintf(
                        'SymbolSeeder: id duplikat "%s" di %s, dilewati.',
                        $record['id'],
                        File::basename($path)
                    ));

                    continue;
                }

                $seen[$record['id']] = true;
                $records[] = $this->normalize($record);
                $added++;
            }

            $this->command->line(sprintf(
                '  %-16s %3d record',
                File::basename($path),
                $added
            ));
        }

        return $records;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function normalize(array $record): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $value = $record[$column] ?? null;

            if (in_array($column, self::JSON_FIELDS, true)) {
                $row[$column] = $value === null ? null : json_encode(array_values((array) $value), JSON_UNESCAPED_UNICODE);

                continue;
            }

            $row[$column] = is_scalar($value) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $row;
    }
}
