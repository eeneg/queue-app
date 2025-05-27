<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'prefix',
        'description',
        'requirements',
        'active',
    ];

    protected $casts = [
        'requirements' => 'json',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeActive(Builder $query, bool $active = true): Builder
    {
        return $query->where('active', $active);
    }
}
