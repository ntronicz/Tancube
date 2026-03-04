<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Performance optimization: Add indexes to tasks and activity_logs tables
     * to support efficient queries at scale.
     */
    public function up(): void
    {
        // Add indexes to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_date');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('created_by');
            
            // Composite indexes for common query patterns
            $table->index(['organization_id', 'status'], 'tasks_org_status_index');
            $table->index(['organization_id', 'due_date'], 'tasks_org_due_date_index');
            $table->index(['assigned_to', 'status'], 'tasks_agent_status_index');
        });

        // Add indexes to activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('organization_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('timestamp');
            
            // Composite index for common queries
            $table->index(['organization_id', 'timestamp'], 'activity_logs_org_timestamp_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['created_by']);
            $table->dropIndex('tasks_org_status_index');
            $table->dropIndex('tasks_org_due_date_index');
            $table->dropIndex('tasks_agent_status_index');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['action']);
            $table->dropIndex(['timestamp']);
            $table->dropIndex('activity_logs_org_timestamp_index');
        });
    }
};
