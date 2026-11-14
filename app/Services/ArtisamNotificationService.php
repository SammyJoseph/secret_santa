<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArtisamNotificationService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.artisam.base_url', 'https://notifications.artisam.dev'), '/');
        $this->apiKey = config('services.artisam.api_key');
    }

    /**
     * Send a push notification through Artisam Notifications.
     *
     * @param string $title Notification title (required)
     * @param string $message Notification body message (required)
     * @param array $options Optional payload parameters (url, url_title, priority, category, etc.)
     * @return bool
     */
    public function send(string $title, string $message, array $options = []): bool
    {
        if (empty($this->apiKey)) {
            Log::info('Artisam Notifications: API Key not set. Notification skipped.');
            return false;
        }

        $payload = array_merge([
            'title' => $title,
            'message' => $message,
            'priority' => 0,
        ], $options);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(5)
                ->acceptJson()
                ->post("{$this->baseUrl}/api/v1/notify", $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Artisam Notifications failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Artisam Notifications exception: ' . $e->getMessage());
            return false;
        }
    }
}
