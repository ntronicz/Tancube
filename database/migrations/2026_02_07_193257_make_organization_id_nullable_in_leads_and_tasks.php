<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting nullable to not-null requires no null values exist
        Schema::table('leads', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable(false)->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable(false)->change();
        });
    }
};
