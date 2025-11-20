<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketService
{
    public function getUsedTicketsForEvent(int $eventId, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Ticket::query()
            ->whereHas('invitation', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->where('status', 'used')
            ->with('invitation') // Eager load to prevent N+1 queries.
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
