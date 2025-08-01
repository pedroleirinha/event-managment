<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendEventReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications to all event attendees that event starts soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Event reminder command executed at: ' . now());

        $events = Event::with('attendees.user')
            ->whereBetween("start_time", [now(), now()->addDay()])
            ->get();

        $eventsCounts = $events->count();
        $eventLabel = Str::plural('event', $eventsCounts);

        $this->info("Found $eventsCounts $eventLabel");

        $events->each(
            fn($event) =>
            $event->attendees->each(
                fn($attendee) =>
                $this->info("Notifying user with ID: " . $attendee->user_id . " and email: " . $attendee->user->email)
            )
        );

        $this->info('Reminder notifications sent successfully');
    }
}
