<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_result_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamps();

            $table->index(['semester_result_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_result_audits');
    }
};
