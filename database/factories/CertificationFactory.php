<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certification;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'cert_type' => fake()->randomElement(['Serdos', 'Keahlian BNSP', 'Profesi']),
            'cert_number' => 'CERT-'.fake()->numerify('##########'),
            'issued_by' => 'Kementerian Pendidikan Kebudayaan Riset dan Teknologi',
            'issued_date' => now()->subYear()->toDateString(),
            'expiry_date' => now()->addYears(4)->toDateString(),
            'certificate_path' => 'certs/'.fake()->uuid().'.pdf',
        ];
    }
}
