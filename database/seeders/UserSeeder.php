<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'email_verified_at' => now()->format('Y-m-d H:i:s.u'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'email_verified_at' => now()->format('Y-m-d H:i:s.u'),
        ]);
        $admin->assignRole('Admin');
    }
}