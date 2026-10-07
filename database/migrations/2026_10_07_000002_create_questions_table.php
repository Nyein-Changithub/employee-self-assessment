<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_cycle_id')->constrained('assessment_cycles')->cascadeOnDelete();
            $table->text('question_en');
            $table->text('question_mm');
            $table->text('guide_en')->nullable();
            $table->text('guide_mm')->nullable();
            $table->integer('order_no')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
