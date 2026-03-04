<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDeliveryLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Trigger webhooks for a specific event and resource.
     *
     * @param string $event The event name (e.g., 'lead.created')
     * @param mixed $resource The resource model (e.g., Lead, Task)
     * @param array $data Additional data to send
     */
    public function trigger(string $event, $resource, array $data = [])
    {
        $organizationId = $resource->organization_id ?? null;

        if (!$organizationId) {
            return;
        }

        Log::info("Webhook Triggered: Event=[{$event}]", ['resource_id' => $resource->id]);

        $webhooks = Webhook::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get();

        Log::info("Webhook: Found {$webhooks->count()} active webhooks for Org [{$organizationId}].");

        /** @var Webhook $webhook */
        foreach ($webhooks as $webhook) {
            if ($webhook->shouldTrigger($event)) {
                Log::info("Webhook: Dispatching to [{$webhook->url}]");
                $this->dispatch($webhook, $event, $resource, $data);
            }
        }
    }

    /**
     * Dispatch a single webhook 
     */
    protected function dispatch(Webhook $webhook, string $event, $resource, array $data)
    {
        try {
            $payload = [
                'event' => $event,
                'timestamp' => now()->toIso8601String(),
                'data' => array_merge($resource->toArray(), $data),
            ];

            Log::info("Webhook Dispatching [{$event}]: " . json_encode($payload));

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(10)->post($webhook->url, $payload);

            $this->logDelivery($webhook, $event, $resource, $response->status(), null);

        } catch (\Exception $e) {
            Log::error("Webhook failed: {$e->getMessage()}");
            $this->logDelivery($webhook, $event, $resource, 500, $e->getMessage());
        }
    }

    /**
     * Log webhook delivery attempt, wrapped in try/catch to prevent cascading failures.
     */
    protected function logDelivery(Webhook $webhook, string $event, $resource, int $statusCode, ?string $error)
    {
        try {
            WebhookDeliveryLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'resource_type' => get_class($resource),
                'resource_id' => $resource->id,
                'status_code' => $statusCode,
                'error_message' => $error,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log webhook delivery: {$e->getMessage()}");
        }
    }
}
