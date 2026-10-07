<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('assessment_cycles', 'assessment_periods');

        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('assessment_cycle_id', 'assessment_period_id');
        });
        Schema::table('assessments', function (Blueprint $table) {
            $table->renameColumn('assessment_cycle_id', 'assessment_period_id');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->renameColumn('assessment_period_id', 'assessment_cycle_id');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('assessment_period_id', 'assessment_cycle_id');
        });

        Schema::rename('assessment_periods', 'assessment_cycles');
    }
};
