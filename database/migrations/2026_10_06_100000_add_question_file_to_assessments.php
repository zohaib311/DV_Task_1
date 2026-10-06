<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('question_file_path')->nullable()->after('instructions');
            $table->string('question_original_filename')->nullable()->after('question_file_path');
            $table->string('question_mime_type', 150)->nullable()->after('question_original_filename');
            $table->unsignedBigInteger('question_file_size')->nullable()->after('question_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', fn (Blueprint $table) => $table->dropColumn([
            'question_file_path', 'question_original_filename', 'question_mime_type', 'question_file_size',
        ]));
    }
};
