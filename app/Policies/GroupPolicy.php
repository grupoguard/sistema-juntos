<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('groups.view');
    }

    public function view(User $user, Group $group): bool
    {
        if (! $user->can('groups.view')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($group->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('groups.create');
    }

    public function update(User $user, Group $group): bool
    {
        if (! $user->can('groups.edit')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($group->id);
        }

        return false;
    }

    public function delete(User $user, Group $group): bool
    {
        if (! $user->can('groups.delete')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($group->id);
        }

        return false;
    }
}