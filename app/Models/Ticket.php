<?php

namespace App\Models;

use App\Enums\LogStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Ticket extends Model
{
    use HasUlids;

    protected $fillable = [
        'number',
        'priority',
        'service_id',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function assign(User|Counter $user): Transaction
    {
        $user_id = $user instanceof User ? $user->id : $user->user_id;

        $counter_id = $user instanceof Counter ? $user->id : $user->counter->id;

        abort_unless($user_id && $counter_id, 404);

        $ticket = $this->transaction()->create([
            'user_id' => $user_id,
            'counter_id' => $counter_id,
        ]);

        $ticket->log()->create([
            'status' => LogStatus::SERVED,
            'user_id' => Auth::id(),
        ]);

        return $ticket;
    }
}
