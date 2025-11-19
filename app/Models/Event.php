<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'date'];

    protected $casts = ['date' => 'datetime'];

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
