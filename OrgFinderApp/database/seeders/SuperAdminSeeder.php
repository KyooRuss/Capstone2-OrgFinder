<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@orgfinder.com'],
            [
                'name'     => 'Russell Brian Silagan',
                'password' => Hash::make('password'),
                'role'     => 'super_admin',
                'status'   => 'active',
            ]
        );
    }
}
