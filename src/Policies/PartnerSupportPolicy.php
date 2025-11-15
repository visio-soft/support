<?php

namespace VisioSoft\Support\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use VisioSoft\Support\Models\PartnerSupport;

class PartnerSupportPolicy
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
    public function view($user, PartnerSupport $partnerSupport): bool
    {
        // Users can view their own tickets or if they are admin
        return $user->id === $partnerSupport->user_id || $this->isAdmin($user);
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
    public function update($user, PartnerSupport $partnerSupport): bool
    {
        // Users can only update their own open tickets
        return $user->id === $partnerSupport->user_id && $partnerSupport->isOpen();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, PartnerSupport $partnerSupport): bool
    {
        // Users can only delete their own tickets, admins can delete any
        return $user->id === $partnerSupport->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore($user, PartnerSupport $partnerSupport): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete($user, PartnerSupport $partnerSupport): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can assign tickets.
     */
    public function assign($user, PartnerSupport $partnerSupport): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can close tickets.
     */
    public function close($user, PartnerSupport $partnerSupport): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can reopen tickets.
     */
    public function reopen($user, PartnerSupport $partnerSupport): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can add replies.
     */
    public function addReply($user, PartnerSupport $partnerSupport): bool
    {
        return $user->id === $partnerSupport->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can add internal notes.
     */
    public function addInternalNote($user, PartnerSupport $partnerSupport): bool
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
