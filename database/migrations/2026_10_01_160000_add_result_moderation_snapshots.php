<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_results', function (Blueprint $table) {
            $table->string('source', 30)->default('manual');
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
        });
        Schema::table('semester_result_items', function (Blueprint $table) {
            $table->foreignId('assessment_submission_id')->nullable()->constrained()->restrictOnDelete();
            $table->json('assessment_snapshot')->nullable();
        });
        Schema::table('semester_result_audits', fn (Blueprint $table) => $table->text('reason')->nullable());
        Role::where('name', 'Academic Admin')->where('guard_name', 'web')->first()?->givePermissionTo(Permission::findOrCreate('results.approve', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('semester_result_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assessment_submission_id');
            $table->dropColumn('assessment_snapshot');
        });
        Schema::table('semester_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['source', 'revision', 'reviewed_at']);
        });
        Schema::table('semester_result_audits', fn (Blueprint $table) => $table->dropColumn('reason'));
    }
};
