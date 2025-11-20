<?php

namespace App\Services;

use App\Models\Invitation;
use App\Queries\InvitationSearchQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class InvitationSearchService
{
    public function search(array $filters, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $query = Invitation::query()->with('event');

        $searchQuery = new InvitationSearchQuery($query);
        $searchQuery->apply($filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }
}
