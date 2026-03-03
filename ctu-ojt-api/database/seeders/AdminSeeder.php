<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@ctu-ojt.com'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@ctu-ojt.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create sample supervisor
        User::updateOrCreate(
            ['email' => 'supervisor@company.com'],
            [
                'name' => 'Sample Supervisor',
                'email' => 'supervisor@company.com',
                'password' => Hash::make('supervisor123'),
                'role' => 'supervisor',
                'is_active' => true,
            ]
        );
    }
}
