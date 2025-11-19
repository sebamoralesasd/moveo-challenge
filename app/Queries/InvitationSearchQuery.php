<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class InvitationSearchQuery
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function apply(array $filters): Builder
    {
        if (isset($filters['event_id'])) {
            $this->filterByEvent($filters['event_id']);
        }

        if (isset($filters['sector'])) {
            $this->filterBySector($filters['sector']);
        }

        if (isset($filters['date_from'])) {
            $this->filterByDateFrom($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->filterByDateTo($filters['date_to']);
        }

        return $this->query;
    }

    protected function filterByEvent(int $eventId): void
    {
        $this->query->where('event_id', $eventId);
    }

    protected function filterBySector(string $sector): void
    {
        $this->query->where('sector', $sector);
    }

    protected function filterByDateFrom(string $date): void
    {
        $this->query->whereDate('created_at', '>=', $date);
    }

    protected function filterByDateTo(string $date): void
    {
        $this->query->whereDate('created_at', '<=', $date);
    }
}
