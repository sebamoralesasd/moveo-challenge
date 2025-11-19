<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalInvitationService
{
    private const int CACHE_TTL = 600;

    private const int TIMEOUT_SECONDS = 5;

    private const int RETRY_TIMES = 3;

    private const int RETRY_SLEEP_MS = 200;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getInvitation(string $hash): array
    {
        $baseUrl = config('services.invitations.base_url');
        $token = config('services.invitations.token');
        $cacheKey = "invitation:{$hash}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($baseUrl, $token, $hash) {
            Log::info("Fetching external invitation for hash: {$hash}");
            try {
                $response = Http::withToken($token)
                    ->timeout(self::TIMEOUT_SECONDS)
                    ->retry(self::RETRY_TIMES, self::RETRY_SLEEP_MS)
                    ->get("{$baseUrl}/{$hash}")
                    ->throw();

                Log::info("Successfully fetched invitation [{$hash}]");

                return $response->json();
            } catch (\Exception $e) {
                Log::error("Failed to fetch invitation [{$hash}]: ".$e->getMessage());
                throw $e;
            }
        });
    }
}
