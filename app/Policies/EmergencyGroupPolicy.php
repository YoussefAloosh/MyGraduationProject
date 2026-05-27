<?php

namespace App\Policies;

use App\Models\EmergencyGroup;
use App\Models\User;

class EmergencyGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'emergency_manager']);
    }

    public function view(User $user, EmergencyGroup $group): bool
    {
        return $user->hasAnyRole(['admin', 'emergency_manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'emergency_manager']);
    }

    public function update(User $user, EmergencyGroup $group): bool
    {
        return $user->hasAnyRole(['admin', 'emergency_manager']);
    }

    public function delete(User $user, EmergencyGroup $group): bool
    {
        return $user->hasRole('admin');
    }

    public function toggleActive(User $user, EmergencyGroup $group): bool
    {
        return $user->hasAnyRole(['admin', 'emergency_manager']);
    }
}