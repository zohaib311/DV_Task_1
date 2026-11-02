<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->string('day_of_week'); // Monday, Tuesday, Wednesday, Thursday, Friday, Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->string('slot_type')->default('regular'); // regular, lab_double, break
            $table->timestamps();

            $table->unique(['academic_term_id', 'day_of_week', 'start_time', 'end_time'], 'term_day_time_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
