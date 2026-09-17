<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('section_id');

            $table->decimal('percentage', 5, 2); // e.g. 85.50
            $table->decimal('gpa', 3, 2);        // e.g. 3.70
            $table->decimal('cgpa', 3, 2);       // e.g. 3.50
            $table->string('grade');             // e.g. A, B+, C
            $table->enum('status', ['Pass', 'Fail'])->default('Pass');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
