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
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('phone_normalized')->nullable()->index();
            $table->string('email')->nullable();
            $table->string('source')->nullable();
            $table->string('course')->nullable();
            $table->string('status')->default('NEW');
            $table->uuid('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('next_follow_up')->nullable();
            $table->dateTime('last_contacted')->nullable();
            $table->integer('follow_up_alert_count')->default(0);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            
            // Index for duplicate detection
            $table->index(['organization_id', 'phone_normalized']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
