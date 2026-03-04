<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Performance optimization: Add indexes for columns frequently used in
     * WHERE clauses, ORDER BY, and JOIN operations to handle 200K+ leads.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Single column indexes for common filters
            $table->index('status');
            $table->index('next_follow_up');
            $table->index('created_at');
            $table->index('assigned_to');
            $table->index('source');
            $table->index('course');

            // Composite indexes for common query patterns
            // Optimizes: WHERE organization_id = ? AND status = ?
            $table->index(['organization_id', 'status'], 'leads_org_status_index');
            
            // Optimizes: WHERE organization_id = ? ORDER BY next_follow_up
            $table->index(['organization_id', 'next_follow_up'], 'leads_org_followup_index');
            
            // Optimizes: WHERE organization_id = ? AND assigned_to = ? AND status = ?
            $table->index(['organization_id', 'assigned_to', 'status'], 'leads_org_agent_status_index');
            
            // Optimizes: WHERE organization_id = ? ORDER BY created_at DESC
            $table->index(['organization_id', 'created_at'], 'leads_org_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop single column indexes
            $table->dropIndex(['status']);
            $table->dropIndex(['next_follow_up']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['source']);
            $table->dropIndex(['course']);

            // Drop composite indexes
            $table->dropIndex('leads_org_status_index');
            $table->dropIndex('leads_org_followup_index');
            $table->dropIndex('leads_org_agent_status_index');
            $table->dropIndex('leads_org_created_index');
        });
    }
};
