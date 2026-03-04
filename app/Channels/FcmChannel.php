<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            // Check if the user has an FCM token
            if (! $notifiable->fcm_token) {
                return;
            }

            // Get the payload from the notification's toFcm method
            if (! method_exists($notification, 'toFcm')) {
                Log::warning('FCM Notification does not have toFcm method: ' . get_class($notification));
                return;
            }

            $payload = $notification->toFcm($notifiable);
            $this->sendToFirebase($notifiable->fcm_token, $payload);
        } catch (\Throwable $e) {
            Log::warning('FCM notification failed (non-critical): ' . $e->getMessage());
        }
    }

    /**
     * Generate an OAuth2 token and send the FCM request using HTTP v1 API.
     */
    protected function sendToFirebase(string $fcmToken, array $payload)
    {
        // Requires a service account JSON file
        $serviceAccountPath = storage_path('app/firebase-credentials.json');

        if (! file_exists($serviceAccountPath)) {
            Log::error('FCM Error: firebase-credentials.json not found in storage/app/');
            return;
        }

        $credentials = json_decode(file_get_contents($serviceAccountPath), true);
        $projectId = $credentials['project_id'] ?? null;

        if (! $projectId) {
            Log::error('FCM Error: Invalid firebase-credentials.json');
            return;
        }

        $accessToken = $this->getAccessToken($credentials);

        if (! $accessToken) {
            Log::error('FCM Error: Failed to generate OAuth2 Access Token');
            return;
        }

        $response = Http::withToken($accessToken)->post(
            "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
            [
                'message' => array_merge([
                    'token' => $fcmToken,
                ], $payload)
            ]
        );

        if (! $response->successful()) {
            Log::error('FCM Error Response: ' . $response->body());
        } else {
            Log::info("FCM Notification sent successfully to {$fcmToken}");
        }
    }

    /**
     * Generates a short-lived OAuth2 Token using pure PHP with the service account JWT.
     */
    protected function getAccessToken(array $credentials)
    {
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], "sha256WithRSAEncryption");
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        return $response->json('access_token');
    }
}
