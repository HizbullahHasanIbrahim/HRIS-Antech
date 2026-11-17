<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'admin_bsdm', 'kepala_unit', 'pegawai', 'asesor_bkd', 'tim_rekrutmen',
                'pimpinan_universitas', 'staf_layanan', 'verifikator'
            ]),
            'description' => fake('id_ID')->sentence(),
        ];
    }
}