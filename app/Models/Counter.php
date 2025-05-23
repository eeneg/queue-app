<?php

namespace App\Models;

use App\Enums\LogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counter extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'active',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)
            ->latest();
    }

    public function transaction(): HasOne
    {
        return $this->transactions()
            ->whereHas('ticket', fn ($query) => $query->whereDate('created_at', now()))
            ->one()
            ->ofMany(
                ['id' => 'max'],
                fn (Builder $query) => $query
                    ->whereRelation('log', 'status', LogStatus::SERVED)
            );
    }

    public function dailyTransactions(): HasMany
    {
        return $this->transactions()
            ->whereHas('ticket', fn ($query) => $query->whereDate('created_at', now()))
            ->latest();
    }
}
