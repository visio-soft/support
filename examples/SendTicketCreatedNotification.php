<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use VisioSoft\Support\Events\SupportTicketCreated;

/**
 * Example listener for sending notifications when a support ticket is created
 */
class SendTicketCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SupportTicketCreated $event): void
    {
        $ticket = $event->ticket;

        // Example 1: Send email to admins
        // Mail::to('admin@example.com')->send(new TicketCreatedMail($ticket));

        // Example 2: Send notification to all admin users
        // $admins = User::where('is_admin', true)->get();
        // Notification::send($admins, new NewTicketNotification($ticket));

        // Example 3: Log the event
        \Log::info('New support ticket created', [
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'subject' => $ticket->subject,
        ]);

        // Example 4: Send Slack notification
        // $this->sendSlackNotification($ticket);
    }

    /**
     * Example method to send Slack notification
     */
    protected function sendSlackNotification($ticket): void
    {
        // Implement your Slack notification logic here
        // You might use Laravel's built-in Slack notification or a webhook
    }
}
