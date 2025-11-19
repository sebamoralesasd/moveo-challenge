<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id', 'code', 'status', 'used_at', 'validated_by',
    ];

    // TODO: validate status enum

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
