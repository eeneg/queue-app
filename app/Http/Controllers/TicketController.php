<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'priority' => 'nullable|boolean',
        ]);

        $service = Service::findOrFail($request->service_id);

        abort_unless($service->active, 403, 'Service is not active');

        $number = $service->tickets()->whereDate('created_at', now())->count() + 1;

        return Ticket::create([
            'service_id' => $service->id,
            'number' => "{$service->prefix}-".str($number)->padLeft(3, '0'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Ticket {$ticket->number}</title>
            </head>
            <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; text-align: center; background: white;">
                <div style="max-width: 400px; margin: 0 auto; background: white;">
                    <div style="font-family: 'Courier New', Courier, monospace; font-size: 3em; font-weight: bold; margin-bottom: 5px; color: #000; letter-spacing: 2px;">
                        {$ticket->number}
                    </div>
                    <div style="font-size: 1.2em; margin-bottom: 2px; color: #333;">
                        {$ticket->service->name}
                    </div>
                    <div style="font-size: 0.9em; color: #666;">
                        {$ticket->created_at->format('Y-m-d H:i')}
                    </div>
                </div>
            </body>
            </html>
        HTML;
    }
}
