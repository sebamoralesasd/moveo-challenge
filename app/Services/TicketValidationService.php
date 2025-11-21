<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;

class TicketValidationService
{
    public function validateTicket(string $code): Ticket
    {
        $ticket = Ticket::where('code', $code)->first();
        if (! $ticket) {
            throw new \Exception("Ticket {$code} not found");
        }
        if ($ticket->status === TicketStatus::USED) {
            throw new \Exception("Ticket {$ticket->code} was already used");
        }

        $ticket->status = TicketStatus::USED;
        $ticket->validated_by = auth()->id();
        $ticket->used_at = now();
        $ticket->save();

        return $ticket;
    }
}
