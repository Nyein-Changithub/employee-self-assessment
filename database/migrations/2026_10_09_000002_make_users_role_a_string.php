<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Custom roles can be created in the admin UI, so the column can no longer be a fixed enum.
        // It is kept in sync with the user's Spatie role ('employee' when they have none).
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'ceo', 'gm', 'admin'])->default('employee')->change();
        });
    }
};
