<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('content');
            $table->string('priority', 20)->default('normal'); // 'normal', 'urgent'
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_offering_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_announcements');
    }
};
