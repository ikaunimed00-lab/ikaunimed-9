<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'aksi' => fake()->randomElement(array_keys(AuditLog::ACTIONS)),
            'subject_type' => null,
            'subject_id' => null,
            'perubahan' => null,
            'ip' => fake()->ipv4(),
        ];
    }
}
