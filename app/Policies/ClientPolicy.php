<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        if (! $user->can('clients.view')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($client->group_id);
        }

        if ($user->isSeller()) {
            $sellerIds = $user->getAccessibleSellerIds();

            if (empty($sellerIds)) return false;

            return Order::query()
                ->where('client_id', $client->id)
                ->whereIn('seller_id', $sellerIds)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        if (! $user->can('clients.edit')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($client->group_id);
        }

        // seller não edita cliente
        return false;
    }

    public function delete(User $user, Client $client): bool
    {
        if (! $user->can('clients.delete')) return false;

        if ($user->isAdmin()) return true;

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($client->group_id);
        }

        return false;
    }
}