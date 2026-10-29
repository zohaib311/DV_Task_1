<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_results', function (Blueprint $table) {
            $table->string('verification_hash', 64)->nullable()->unique()->after('status');
        });

        Schema::table('student_fee_invoices', function (Blueprint $table) {
            $table->string('verification_hash', 64)->nullable()->unique()->after('status');
        });

        // Backfill any existing records
        $results = DB::table('semester_results')->whereNull('verification_hash')->get(['id']);
        foreach ($results as $res) {
            DB::table('semester_results')->where('id', $res->id)->update([
                'verification_hash' => hash('sha256', 'res_' . $res->id . '_' . Str::random(32)),
            ]);
        }

        $invoices = DB::table('student_fee_invoices')->whereNull('verification_hash')->get(['id']);
        foreach ($invoices as $inv) {
            DB::table('student_fee_invoices')->where('id', $inv->id)->update([
                'verification_hash' => hash('sha256', 'inv_' . $inv->id . '_' . Str::random(32)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('semester_results', function (Blueprint $table) {
            $table->dropColumn('verification_hash');
        });

        Schema::table('student_fee_invoices', function (Blueprint $table) {
            $table->dropColumn('verification_hash');
        });
    }
};
