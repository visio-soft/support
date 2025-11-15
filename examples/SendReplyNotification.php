<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use VisioSoft\Support\Events\SupportReplyAdded;

/**
 * Example listener for sending notifications when a reply is added
 */
class SendReplyNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SupportReplyAdded $event): void
    {
        $ticket = $event->ticket;
        $reply = $event->reply;

        // Don't send notifications for internal notes
        if ($reply->is_internal_note) {
            return;
        }

        if ($reply->is_admin_reply) {
            // Notify the customer
            // Mail::to($ticket->user->email)->send(new AdminRepliedMail($ticket, $reply));
            
            \Log::info('Admin replied to ticket', [
                'ticket_id' => $ticket->id,
                'reply_id' => $reply->id,
                'admin_id' => $reply->user_id,
            ]);
        } else {
            // Notify the assigned admin or all admins
            if ($ticket->assigned_to) {
                // Mail::to($ticket->assignedTo->email)->send(new CustomerRepliedMail($ticket, $reply));
            }
            
            \Log::info('Customer replied to ticket', [
                'ticket_id' => $ticket->id,
                'reply_id' => $reply->id,
                'customer_id' => $reply->user_id,
            ]);
        }
    }
}
