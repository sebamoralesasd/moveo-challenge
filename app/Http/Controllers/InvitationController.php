<?php

namespace App\Http\Controllers;

use App\Services\InvitationRedemptionService;
use App\Services\InvitationSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(protected InvitationRedemptionService $redemptionService, protected InvitationSearchService $searchService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['event_id', 'sector', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 10);
        $results = $this->searchService->search($filters, (int) $perPage);

        return response()->json($results);
    }

    public function redeem(string $hash): JsonResponse
    {
        try {
            $invitation = $this->redemptionService->redeem($hash);

            // TODO: change after background job
            return response()->json([
                'data' => $invitation->load('tickets'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
