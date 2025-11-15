<?php

namespace VisioSoft\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use VisioSoft\Support\Enums\SupportPriority;
use VisioSoft\Support\Enums\SupportStatus;

class PartnerSupport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'partner_support';

    protected $fillable = [
        'park_id',
        'user_id',
        'subject',
        'content',
        'status',
        'priority',
        'assigned_to',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'status' => SupportStatus::class,
        'priority' => SupportPriority::class,
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'assigned_to');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'closed_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PartnerSupportReply::class, 'partner_support_id');
    }

    public function publicReplies(): HasMany
    {
        return $this->replies()->where('is_internal_note', false);
    }

    public function internalNotes(): HasMany
    {
        return $this->replies()->where('is_internal_note', true);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            SupportStatus::OPEN->value,
            SupportStatus::IN_PROGRESS->value,
            SupportStatus::WAITING_CUSTOMER->value,
            SupportStatus::WAITING_ADMIN->value,
        ]);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', [
            SupportStatus::RESOLVED->value,
            SupportStatus::CLOSED->value,
        ]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            SupportStatus::OPEN,
            SupportStatus::IN_PROGRESS,
            SupportStatus::WAITING_CUSTOMER,
            SupportStatus::WAITING_ADMIN,
        ]);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [
            SupportStatus::RESOLVED,
            SupportStatus::CLOSED,
        ]);
    }

    public function close($userId): void
    {
        $this->update([
            'status' => SupportStatus::CLOSED,
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => SupportStatus::OPEN,
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }
}
