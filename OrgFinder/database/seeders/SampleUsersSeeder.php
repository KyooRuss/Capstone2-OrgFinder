<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['email' => 'student1@orgfinder.com', 'password' => 'password'],
            ['email' => 'student2@orgfinder.com', 'password' => 'password'],
            ['email' => 'student3@orgfinder.com', 'password' => 'password'],
            ['email' => 'student4@orgfinder.com', 'password' => 'password'],
            ['email' => 'student5@orgfinder.com', 'password' => 'password'],
            ['email' => 'student6@orgfinder.com', 'password' => 'password'],
            ['email' => 'student7@orgfinder.com', 'password' => 'password'],
            ['email' => 'student11@orgfinder.com', 'password' => 'password'],
            ['email' => 'student12@orgfinder.com', 'password' => 'password'],
            ['email' => 'student13@orgfinder.com', 'password' => 'password'],
            ['email' => 'student14@orgfinder.com', 'password' => 'password'],
            ['email' => 'student15@orgfinder.com', 'password' => 'password'],
        ];

        foreach ($users as $user) {
            $existing = User::where('email', $user['email'])->first();
            if ($existing) {
                $existing->update([
                    'password' => Hash::make($user['password']),
                    'status'   => 'active',
                ]);
            } else {
                User::create([
                    'email'      => $user['email'],
                    'first_name' => '',
                    'last_name'  => '',
                    'password'   => Hash::make($user['password']),
                    'role'       => 'student',
                    'status'     => 'active',
                ]);
            }
        }
    }
}
