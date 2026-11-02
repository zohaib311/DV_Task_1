<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'time_slot_id'], 'tt_room_slot_unique');
            $table->unique(['teacher_id', 'time_slot_id'], 'tt_teacher_slot_unique');
            $table->unique(['course_offering_id', 'time_slot_id'], 'tt_offering_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_allocations');
    }
};
