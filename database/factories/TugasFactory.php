<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TugasFactory extends Factory
{
    protected $model = Tugas::class;

    public function definition(): array
    {
        return [
            'judul' => fake()->words(3, true),
            'deskripsi' => fake()->sentence(),
            'kelas_id' => Kelas::factory(),
            'guru_id' => User::factory(),
            'pertemuan_id' => null,
            'tipe' => 'upload',
            'soal' => null,
            'deadline' => now()->addDays(7),
            'batas_jam' => null,
        ];
    }

    public function denganTimer(int $jam = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'batas_jam' => $jam,
            'deadline' => null,
        ]);
    }
}
