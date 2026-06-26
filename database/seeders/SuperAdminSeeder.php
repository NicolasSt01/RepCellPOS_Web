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
            ['email' => 'admin@nexacore.com.mx'],
            [
                'name' => 'Superadmin',
                'password' => Hash::make('Cb15a33a1c.RB'),
                'is_superadmin' => true,
                'is_active' => true,
            ]
        );
    }
}
