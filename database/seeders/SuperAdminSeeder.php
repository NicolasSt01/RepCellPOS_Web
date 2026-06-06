<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@repcellpos.com'],
            [
                'name' => 'Superadmin',
                'password' => Hash::make('password'),
                'is_superadmin' => true,
                'is_active' => true,
            ]
        );
    }
}
