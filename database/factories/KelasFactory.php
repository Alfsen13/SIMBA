<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        return [
            'nama_kelas' => fake()->words(2, true),
            'kode_kelas' => strtoupper(fake()->bothify('??-####')),
            'deskripsi' => fake()->sentence(),
            'kategori' => fake()->randomElement(['Matematika', 'IPA', 'Bahasa Inggris', 'IPS', 'Bahasa Indonesia']),
            'guru_id' => User::factory(),
        ];
    }
}
