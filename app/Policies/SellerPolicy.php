<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sellers.view');
    }

    public function view(User $user, Seller $seller): bool
    {
        if (! $user->can('sellers.view')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($seller->group_id);
        }

        if ($user->isSeller()) {
            return $user->hasAccessToSeller($seller->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('sellers.create');
    }

    public function update(User $user, Seller $seller): bool
    {
        if (! $user->can('sellers.edit')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($seller->group_id);
        }

        return false;
    }

    public function delete(User $user, Seller $seller): bool
    {
        if (! $user->can('sellers.delete')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($seller->group_id);
        }

        return false;
    }
}
