<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasUlids;

    protected $fillable = [
        'number',
        'priority',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
