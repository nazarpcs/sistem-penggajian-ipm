<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk membuat user Admin default.
 */
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ipm.test'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Pemilik PT (Owner) — untuk approval invoice dan monitoring
        User::updateOrCreate(
            ['email' => 'owner@ptabc.co.id'],
            [
                'name' => 'Budi Santoso (Owner)',
                'password' => bcrypt('password'),
                'role' => 'pemilik_pt',
                'is_active' => true,
            ]
        );
    }
}
