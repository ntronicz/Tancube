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
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $table->fullText(['name', 'email', 'course', 'notes']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $table->dropFullText(['name', 'email', 'course', 'notes']);
            }
        });
    }
};
