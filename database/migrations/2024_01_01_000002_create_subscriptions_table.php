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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('vendor_id');
            $table->string('plan_name');
            $table->date('start_date');
            $table->date('expiry_date');
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['MONTHLY', 'QUARTERLY', 'YEARLY'])->default('MONTHLY');
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'CANCELLED'])->default('ACTIVE');
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
