<?php

namespace App\Services;

use App\Models\Ticket;

class TicketValidationService
{
    public function validateTicket(string $code): Ticket
    {
        $ticket = Ticket::where('code', $code)->first();
        if (!$ticket) {
            throw new \Exception("Ticket {$code} not found");
        }
        if ($ticket->status === 'used') {
            throw new \Exception("Ticket {$ticket->code} was already used");
        }

        $ticket->status = 'used';
        $ticket->used_at = now();
        $ticket->save();

        return $ticket;
    }
}
