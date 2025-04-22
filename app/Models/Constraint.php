<?php

namespace App\Models;

use App\Enums\ConstraintTypes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Constraint extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'value',
        'type',
        'active',
    ];

    protected $casts = [
        'value' => 'json',
        'type' => ConstraintTypes::class,
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
