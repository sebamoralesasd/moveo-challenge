<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationRedemptionService;
use App\Services\InvitationSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(protected InvitationRedemptionService $redemptionService, protected InvitationSearchService $searchService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['event_id', 'sector', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $results = $this->searchService->search($filters, (int) $perPage, (int) $page);

        return response()->json($results);
    }

    public function redeem(string $hash): JsonResponse
    {
        try {
            $invitation = $this->redemptionService->redeem($hash);
            $invitation->load('event');

            return response()->json([
                'data' => $invitation,
                'message' => 'Invitation redeemed successfully. Tickets are being generated.',
                'tickets_url' => url("/api/invitations/{$invitation->external_id}/tickets"),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function getTickets(string $hash): JsonResponse
    {
        $invitation = Invitation::where('external_id', $hash)->first();
        if (! $invitation) {
            return response()->json(['error' => "Invitation {$hash} not found"], 404);
        }

        $ticketsCount = $invitation->tickets()->count();

        return response()->json([
            'invitation_id' => $invitation->id,
            'expected_tickets' => $invitation->guest_count,
            'generated_tickets' => $ticketsCount,
            'tickets' => $invitation->tickets->map(fn ($ticket) => [
                'code' => $ticket->code,
                'status' => $ticket->status,
                'used_at' => $ticket->used_at,
        ]),
        ]);
    }
}
