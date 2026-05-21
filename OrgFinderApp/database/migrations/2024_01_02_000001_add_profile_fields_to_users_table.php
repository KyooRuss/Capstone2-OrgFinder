<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('program')->nullable()->after('year_level');
            $table->json('interests')->nullable()->after('program');
            $table->json('skills')->nullable()->after('interests');
            $table->json('activities')->nullable()->after('skills');
            $table->boolean('profile_completed')->default(false)->after('activities');
            $table->string('profile_photo')->nullable()->after('profile_completed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['program', 'interests', 'skills', 'activities', 'profile_completed', 'profile_photo']);
        });
    }
};
