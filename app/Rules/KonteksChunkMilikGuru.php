<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\DocumentChunk;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;

/**
 * Potongan dokumen yang dipilih guru harus benar-benar milik kelasnya: rantai
 * chunk -> document -> materi -> kelas.guru_id, dan dokumennya sudah selesai
 * ditanam. Dokumen yatim (materinya sudah dihapus) ikut tertolak karena teksnya
 * masih bisa terambil oleh pencarian ber-kategori.
 */
class KonteksChunkMilikGuru implements ValidationRule
{
    public function __construct(private int $guruId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $value))));

        if ($ids === []) {
            return;
        }

        $dikenal = DocumentChunk::query()
            ->whereIn('id', $ids)
            ->whereHas('document', function (Builder $query): void {
                $query->where('status', 'completed')
                    ->whereHas('materi', function (Builder $materi): void {
                        $materi->whereHas('kelas', fn (Builder $kelas) => $kelas->where('guru_id', $this->guruId));
                    });
            })
            ->pluck('id')
            ->all();

        $asing = array_values(array_diff($ids, $dikenal));

        if ($asing !== []) {
            $fail('Potongan dokumen #'.implode(', #', $asing).' tidak tersedia untuk kelas Anda.');
        }
    }
}
