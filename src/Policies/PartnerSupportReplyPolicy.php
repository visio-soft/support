<?php

namespace VisioSoft\Support\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use VisioSoft\Support\Models\PartnerSupportReply;

class PartnerSupportReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, PartnerSupportReply $reply): bool
    {
        // Users can view replies on their tickets or if they are admin
        // Internal notes are only visible to admins
        if ($reply->is_internal_note) {
            return $this->isAdmin($user);
        }

        return $user->id === $reply->partnerSupport->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, PartnerSupportReply $reply): bool
    {
        // Only the author can update their own reply within a time window (e.g., 15 minutes)
        if ($user->id !== $reply->user_id) {
            return false;
        }

        // Optional: Add time restriction
        // $editWindow = now()->subMinutes(15);
        // return $reply->created_at->gte($editWindow);

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, PartnerSupportReply $reply): bool
    {
        // Users can delete their own replies, admins can delete any
        return $user->id === $reply->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore($user, PartnerSupportReply $reply): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete($user, PartnerSupportReply $reply): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Check if user is admin.
     * You can customize this method based on your admin check logic.
     */
    protected function isAdmin($user): bool
    {
        // Default implementation - customize based on your needs
        // Examples:
        // return $user->hasRole('admin');
        // return $user->is_admin;
        // return $user->role === 'admin';
        
        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
    }
}
