<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend role enum to include 'prof'
        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin_officer','student','prof') NOT NULL DEFAULT 'student'");

        // Add department to user_profiles
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('department')->nullable()->after('program');
        });

        // Add target_departments to events (JSON array of department strings, null = all)
        Schema::table('events', function (Blueprint $table) {
            $table->json('target_departments')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('target_departments');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('department');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin_officer','student') NOT NULL DEFAULT 'student'");
    }
};
