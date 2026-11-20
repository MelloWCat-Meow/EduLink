<?php

namespace Database\Factories;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<AkunPengguna>
 */
class AkunPenggunaFactory extends Factory
{
    /**
     * The hashed password shared by every factory account.
     */
    protected static ?string $kredensial;

    /**
     * Define the model's default state: a guardian (ortu) account.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'kontak' => fake()->unique()->safeEmail(),
            'kredensial' => static::$kredensial ??= Hash::make('password'),
            'peran' => Peran::Ortu,
        ];
    }

    public function ortu(): static
    {
        return $this->state(fn (array $attributes): array => ['peran' => Peran::Ortu]);
    }

    public function tutor(): static
    {
        return $this->state(fn (array $attributes): array => ['peran' => Peran::Tutor]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => ['peran' => Peran::Admin]);
    }
}
