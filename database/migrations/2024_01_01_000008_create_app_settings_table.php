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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('organization_id');
            $table->string('category'); // sources, statuses, courses
            $table->json('values');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unique(['organization_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
