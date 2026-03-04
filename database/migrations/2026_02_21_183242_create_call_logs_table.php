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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('organization_id');
            $table->string('phone_number', 20);
            $table->enum('call_type', ['INBOUND', 'OUTBOUND', 'MISSED'])->default('OUTBOUND');
            $table->enum('call_status', ['CONNECTED', 'REJECTED', 'NO_ANSWER', 'BUSY', 'UNKNOWN'])->default('CONNECTED');
            $table->integer('duration')->default(0)->comment('Duration in seconds');
            $table->timestamp('call_timestamp')->nullable();
            $table->uuid('lead_id')->nullable()->comment('Auto-matched lead by phone');
            $table->text('notes')->nullable();
            $table->string('device_id', 100)->nullable();
            $table->timestamps();

            // Indexes
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            $table->index(['organization_id', 'call_timestamp']);
            $table->index(['user_id', 'call_timestamp']);
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
