<?php

namespace App\Http\Controllers;

use App\Services\TicketValidationService;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function __construct(protected TicketValidationService $service)
    {
    }

    public function validate(string $code): JsonResponse
    {
        try {
            $ticket = $this->service->validateTicket($code);

            return response()->json([
                'status' => "Ticket {$code} was validated successfully.",
                'used_at' => $ticket->used_at,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
