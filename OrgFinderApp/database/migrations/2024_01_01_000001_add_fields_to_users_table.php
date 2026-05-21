<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin_officer', 'student'])->default('student')->after('email');
            $table->string('student_number')->nullable()->after('role');
            $table->unsignedTinyInteger('year_level')->nullable()->after('student_number');
            $table->enum('status', ['active', 'blocked'])->default('active')->after('year_level');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'student_number', 'year_level', 'status', 'deleted_at']);
        });
    }
};
