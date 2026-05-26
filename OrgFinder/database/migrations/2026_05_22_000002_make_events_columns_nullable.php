<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->time('time')->nullable()->change();
            $table->string('venue')->nullable()->change();
            $table->string('event_poster')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->time('time')->nullable(false)->change();
            $table->string('venue')->nullable(false)->change();
            $table->string('event_poster')->nullable(false)->change();
        });
    }
};
