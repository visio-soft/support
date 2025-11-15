<?php

namespace VisioSoft\Support\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VisioSoft\Support\Models\PartnerSupport;
use VisioSoft\Support\Models\PartnerSupportReply;

class SupportReplyAdded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PartnerSupport $ticket,
        public PartnerSupportReply $reply
    ) {
    }
}
