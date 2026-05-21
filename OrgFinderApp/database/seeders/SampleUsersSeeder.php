<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Sample student with completed profile
        User::updateOrCreate(
            ['email' => 'student1@orgfinder.com'],
            [
                'name'             => 'Maria Santos',
                'password'         => Hash::make('password'),
                'role'             => 'student',
                'student_number'   => '2021-00001',
                'year_level'       => 3,
                'program'          => 'BS Information Technology',
                'interests'        => ['Technology', 'Design', 'Music'],
                'skills'           => ['Programming', 'UI/UX Design'],
                'activities'       => ['Hackathons', 'Seminars'],
                'profile_completed'=> true,
                'status'           => 'active',
            ]
        );

        // Sample student with completed profile
        User::updateOrCreate(
            ['email' => 'student2@orgfinder.com'],
            [
                'name'             => 'Juan dela Cruz',
                'password'         => Hash::make('password'),
                'role'             => 'student',
                'student_number'   => '2022-00002',
                'year_level'       => 2,
                'program'          => 'BS Computer Science',
                'interests'        => ['Sports', 'Technology', 'Volunteering'],
                'skills'           => ['Web Development', 'Public Speaking'],
                'activities'       => ['Sports Fest', 'Community Service'],
                'profile_completed'=> true,
                'status'           => 'active',
            ]
        );

        // Sample student with incomplete profile
        User::updateOrCreate(
            ['email' => 'student3@orgfinder.com'],
            [
                'name'             => 'Ana Reyes',
                'password'         => Hash::make('password'),
                'role'             => 'student',
                'student_number'   => '2023-00003',
                'year_level'       => 1,
                'program'          => null,
                'interests'        => [],
                'skills'           => [],
                'activities'       => [],
                'profile_completed'=> false,
                'status'           => 'active',
            ]
        );

        // Sample admin officer
        User::updateOrCreate(
            ['email' => 'admin@orgfinder.com'],
            [
                'name'             => 'Carlos Mendoza',
                'password'         => Hash::make('password'),
                'role'             => 'admin_officer',
                'status'           => 'active',
                'profile_completed'=> true,
            ]
        );
    }
}
