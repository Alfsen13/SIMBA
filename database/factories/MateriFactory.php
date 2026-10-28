<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriFactory extends Factory
{
    protected $model = Materi::class;

    public function definition(): array
    {
        return [
            'judul' => fake()->words(3, true),
            'bab' => fake()->word(),
            'kelas_id' => Kelas::factory(),
            'guru_id' => User::factory(),
            'file_path' => null,
            'file_type' => null,
        ];
    }
}
