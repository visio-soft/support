<?php

namespace App\Policies;

use App\Models\User;
use VisioSoft\Support\Models\PartnerSupport;
use VisioSoft\Support\Policies\PartnerSupportPolicy as BasePolicy;

/**
 * Example custom policy extending the base policy
 * This shows how to customize the admin check logic
 */
class CustomPartnerSupportPolicy extends BasePolicy
{
    /**
     * Custom admin check logic
     * Customize this based on your application's user model and roles
     */
    protected function isAdmin($user): bool
    {
        // Example 1: Check if user has admin role using Spatie Laravel Permission
        // return $user->hasRole('admin');

        // Example 2: Check if user has a specific permission
        // return $user->hasPermissionTo('manage support tickets');

        // Example 3: Check a boolean field on the user model
        // return $user->is_admin === true;

        // Example 4: Check if user belongs to admin group
        // return $user->group === 'admin';

        // Example 5: Check multiple roles
        // return $user->hasAnyRole(['admin', 'support', 'manager']);

        // Default implementation
        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
    }

    /**
     * Example: Allow support users to manage tickets
     */
    public function viewAny($user): bool
    {
        // Allow if user is admin or has support role
        return $this->isAdmin($user) || $user->hasRole('support');
    }

    /**
     * Example: Custom logic for assigning tickets
     */
    public function assign($user, PartnerSupport $partnerSupport): bool
    {
        // Only admins and support managers can assign tickets
        return $this->isAdmin($user) || $user->hasRole('support-manager');
    }
}
