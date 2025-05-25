<?php

namespace App\Http\Middleware;

use App\Enums\LogStatus;
use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CancelStaleTickets
{
    /**
     * The name of the cache key for the last run time.
     *
     * @var string
     */
    protected static string $cacheKey = 'stale_tickets_cleanup_last_run';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lastRun = Cache::get(static::$cacheKey);

        if (!$lastRun || $lastRun->addHours(6)->isPast()) {
            $this->cancelStaleTickets();

            Cache::put(static::$cacheKey, now(), now()->addHours(6));
        }

        return $next($request);
    }

    private function cancelStaleTickets(): void
    {
        $staleTickets = Ticket::query()
            ->whereDate('created_at', '<', now())
            ->where(function ($query) {
                $query->whereDoesntHave('transaction')
                    ->orWhereHas('transaction', fn ($query) => $query->whereRelation('log', 'status', LogStatus::SKIPPED));
            });

        if ($staleTickets->exists()) {
            DB::transaction(function () use ($staleTickets) {
                $staleTickets->lazyById()->each(function (Ticket $ticket) {
                    $ticket
                        ->transaction()
                        ->create(['remarks' => 'Cancelled due to being stale'])
                        ->logs()
                        ->create(['status' => LogStatus::CANCELLED]);
                });
            }, 3);
        }
    }
}
