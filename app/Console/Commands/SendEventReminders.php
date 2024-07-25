<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminder;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with("attendees.user")->whereBetween("start_time",[now(),now()->addDays(3)])->get();
        $eventCount = $events->count();
        $eventLabel = Str::plural("event", $eventCount);
        $this->info("found {$eventCount} {$eventLabel}");

        $events->each(
            fn ($event) => $event->attendees->each(fn ($attendee) => $attendee->user->notify(new EventReminder($event)))
        );

        $this->info('reminder!!!');
    }
}
