<?php

namespace Tests\Feature;

use App\Services\ExternalInvitationService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Config::set('services.invitations.base_url', 'https://test-api.com/api/invitations');
    Config::set('services.invitations.token', 'test-token');
    Cache::flush();
});

it('fetches invitation data successfully', function () {
    $hash = 'a8f22d';
    $expectedResponse = [
        'invitation_id' => 'a8f22d',
        'event_name' => 'Test Event',
        'event_date' => '2026-12-10 21:00:00',
        'guest_count' => 3,
        'sector' => 'VIP'
    ];

    Http::fake([
        'test-api.com/api/invitations/' . $hash =>
            Http::response($expectedResponse, 200)
    ]);

    $service = new ExternalInvitationService();
    $result = $service->getInvitation($hash);

    expect($result)->toEqual($expectedResponse);

    Http::assertSent(function ($request) use ($hash) {
        return $request->url() === 'https://test-api.com/api/invitations/' . $hash &&
               $request->hasHeader('Authorization', 'Bearer test-token') &&
               $request->method() === 'GET';
    });
});

it('throws RequestException for 404 response', function () {
    $hash = 'non-existent-hash';

    Http::fake([
        'test-api.com/api/invitations/' . $hash =>
            Http::response(['error' => 'Invitation not found'], 404)
    ]);
    $service = new ExternalInvitationService();

    expect(fn () => $service->getInvitation($hash))
        ->toThrow(RequestException::class);
});

it('throws RequestException for 500 server error', function () {
    $hash = 'a8f22d';

    Http::fake([
        'test-api.com/api/invitations/' . $hash =>
            Http::sequence() // Use sequence to simulate persistent failure for retry logic
                ->pushStatus(500)
                ->pushStatus(500)
                ->pushStatus(500)
                ->pushStatus(500)
    ]);
    $service = new ExternalInvitationService();

    expect(fn () => $service->getInvitation($hash))
        ->toThrow(RequestException::class);
});

it('handles network timeout failures', function () {
    $hash = 'a8f22d';

    Http::fake([
        'test-api.com/api/invitations/' . $hash =>
            fn () => throw new ConnectionException('Connection timeout')
    ]);
    $service = new ExternalInvitationService();

    expect(fn () => $service->getInvitation($hash))
        ->toThrow(ConnectionException::class);
});

it('caches the invitation response', function () {
    $hash = 'cache-test-hash';

    Http::fake([
        '*' => Http::response(['id' => $hash], 200)
    ]);

    $service = new ExternalInvitationService();

    $service->getInvitation($hash);
    $service->getInvitation($hash);

    Http::assertSentCount(1);
    expect(Cache::has("invitation:{$hash}"))->toBeTrue();
});

it('retries failed requests for transient errors', function () {
    $hash = 'retry-test-hash';

    Http::fake([
        '*' => Http::sequence()
            ->pushStatus(500)
            ->pushStatus(500)
            ->push(['id' => $hash], 200)
    ]);

    $service = new ExternalInvitationService();
    $result = $service->getInvitation($hash);

    expect($result['id'])->toBe($hash);
});
