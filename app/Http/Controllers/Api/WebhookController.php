<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebhookController extends Controller
{
    /**
     * Get all webhooks
     */
    public function index()
    {
        $user = Auth::user();
        $webhooks = Webhook::where('organization_id', $user->organization_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($webhooks);
    }

    /**
     * Store a new webhook
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:500',
            'events' => 'required|array',
            'events.*' => 'string|in:lead.created,lead.updated,lead.deleted,task.created,task.completed',
        ]);

        $user = Auth::user();

        $webhook = Webhook::create([
            'organization_id' => $user->organization_id,
            'url' => $request->url,
            'events' => $request->events,
            'is_active' => true,
        ]);

        ActivityLog::log('WEBHOOK_CREATED', "Created webhook: {$request->url}");

        return response()->json($webhook, 201);
    }

    /**
     * Show a webhook
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $webhook = Webhook::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        return response()->json($webhook);
    }

    /**
     * Update a webhook
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array',
            'events.*' => 'string|in:lead.created,lead.updated,lead.deleted,task.created,task.completed',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $webhook = Webhook::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $webhook->update($request->only(['url', 'events', 'is_active']));

        ActivityLog::log('WEBHOOK_UPDATED', "Updated webhook: {$webhook->url}");

        return response()->json($webhook);
    }

    /**
     * Delete a webhook
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $webhook = Webhook::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $webhookUrl = $webhook->url;
        $webhook->delete();

        ActivityLog::log('WEBHOOK_DELETED', "Deleted webhook: {$webhookUrl}");

        return response()->json(['message' => 'Webhook deleted successfully']);
    }

    /**
     * Test a webhook
     */
    public function test(string $id)
    {
        try {
            $user = Auth::user();
            $webhook = Webhook::where('organization_id', $user->organization_id)
                ->findOrFail($id);

            $success = $webhook->trigger('test', [
                'message' => 'This is a test webhook from Tancube CRM',
                'triggered_by' => $user->email,
            ]);

            if ($success) {
                return response()->json(['message' => 'Webhook test successful! Target received the payload.']);
            }

            return response()->json([
                'message' => 'Webhook triggered, but target URL returned an error (non-200 status).',
                'warning' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error triggering webhook test: ' . $e->getMessage()
            ], 500);
        }
    }
}
