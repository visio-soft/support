<?php

namespace VisioSoft\Support\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VisioSoft\Support\Models\PartnerSupport;

class SupportTicketAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PartnerSupport $ticket,
        public int $assignedToUserId
    ) {
    }
}
