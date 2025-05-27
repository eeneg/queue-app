@push('styles')
@vite('resources/css/app.css')
@endpush

<x-filament-panels::page class="fi-dashboard-page">
    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    {{-- Main Content Layout --}}
    <div class="grid grid-cols-12 gap-12 min-h-[70vh]">
        {{-- Now Serving Section --}}
        <div class="col-span-4" wire:poll.2s>
            <div class="rounded-xl h-full">
                <div class="py-6 border-b">
                    <h2 class="text-2xl font-bold flex items-center">
                        <div class="w-4 h-4 rounded-full mr-3 animate-pulse bg-green-500"></div>
                        Serving
                    </h2>
                </div>

                <div class="py-6 space-y-6">
                    {{-- Currently Being Served --}}
                    <div class="space-y-4">
                        @foreach ($this->counters as $counter)
                            <div class="rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-2xl font-medium">{{ $counter->name }}</div>
                                    <div class="w-2 h-2 rounded-full animate-pulse"></div>
                                </div>

                                <div class="text-7xl font-semibold font-mono mb-1">
                                    {{ $counter->transaction?->ticket->number }}
                                </div>

                                <div class="text-sm">
                                    {{ $counter->user?->name}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Queue List Section --}}
        <div class="col-span-3">
            <div class="rounded-xl h-full">
                <div class="p-6 border-b">
                    <h2 class="text-2xl font-bold">Queue</h2>
                </div>

                <div class="p-4 space-y-8">
                    {{-- Next Up (Priority) --}}
                    <div class="space-y-4">
                        <div class="text-xs font-semibold uppercase tracking-wider mb-2 underline">
                            Next Up
                        </div>

                        <div class="space-y-1">
                            @foreach ($this->queue->take(3) as $ticket)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold font-mono text-3xl">
                                            {{ $ticket->number }}
                                        </div>

                                        <div class="text-sm">
                                            {{ $ticket->service->name }}
                                        </div>
                                    </div>
                                    <div class="text-sm">
                                        Next in line
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($this->queue->skip(3)->take(7)->isNotEmpty())
                        <div class="space-y-4">
                            <div class="text-xs font-semibold uppercase tracking-wider mb-2 underline">
                                Waiting
                            </div>

                            <div class="space-y-1">
                                @foreach ($this->queue->skip(3)->take(7) as $ticket)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold font-mono text-3xl">
                                                {{ $ticket->number }}
                                            </div>

                                            <div class="text-sm">
                                                {{ $ticket->service->name }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($this->queue->count() > 10)
                                <div class="text-center py-2 tracking-tighter italic">
                                    <div class="text-gray-500 dark:text-gray-500">
                                        ... and {{ $this->queue->count() - 10 }}
                                        more {{ str('ticket')->plural($this->queue->count() - 10) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>


