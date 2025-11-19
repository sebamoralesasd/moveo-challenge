<?php

namespace App\Http\Controllers;

use App\Services\InvitationRedemptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(protected InvitationRedemptionService $service)
    {
    }

    public function redeem(string $hash): JsonResponse
    {
        try {
            $invitation = $this->service->redeem($hash);

            // TODO: change after background job
            return response()->json([
                'data' => $invitation->load('tickets')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
