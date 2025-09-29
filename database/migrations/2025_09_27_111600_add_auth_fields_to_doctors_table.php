<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->string('password')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'password']);
        });
    }
};
