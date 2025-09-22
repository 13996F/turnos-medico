<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('patient_first_name');
            $table->string('patient_last_name');
            $table->string('phone');
            $table->string('dni', 20);
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['requested', 'arrived', 'paid', 'completed'])->default('requested');
            $table->timestamps();

            $table->index(['doctor_id', 'scheduled_at']);
            $table->index(['specialty_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
