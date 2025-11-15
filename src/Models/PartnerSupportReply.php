<?php

namespace VisioSoft\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerSupportReply extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'partner_support_replies';

    protected $fillable = [
        'partner_support_id',
        'user_id',
        'content',
        'is_admin_reply',
        'is_internal_note',
        'attachments',
    ];

    protected $casts = [
        'is_admin_reply' => 'boolean',
        'is_internal_note' => 'boolean',
        'attachments' => 'array',
    ];

    public function partnerSupport(): BelongsTo
    {
        return $this->belongsTo(PartnerSupport::class, 'partner_support_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal_note', true);
    }

    public function scopeAdminReplies($query)
    {
        return $query->where('is_admin_reply', true);
    }

    public function scopeCustomerReplies($query)
    {
        return $query->where('is_admin_reply', false);
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentUrls(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        $disk = config('support.attachments.disk', 'public');
        $urls = [];

        foreach ($this->attachments as $attachment) {
            $urls[] = \Storage::disk($disk)->url($attachment);
        }

        return $urls;
    }
}
