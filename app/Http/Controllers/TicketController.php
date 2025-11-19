<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use App\Services\TicketValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        protected TicketValidationService $validationService,
        protected TicketService $ticketService
    ) {}

    public function validate(string $code): JsonResponse
    {
        try {
            $ticket = $this->validationService->validateTicket($code);

            return response()->json([
                'status' => "Ticket {$code} was validated successfully.",
                'used_at' => $ticket->used_at,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function getUsed(Request $request, int $eventId): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $tickets = $this->ticketService->getUsedTicketsForEvent($eventId, (int) $perPage);

        return response()->json($tickets);
    }
}
