<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
     public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        if (! $user->can('orders.view')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($order->group_id);
        }

        if ($user->isSeller()) {
            return $user->hasAccessToSeller($order->seller_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        if (! $user->can('orders.edit')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($order->group_id);
        }

        // seller por enquanto não edita? ajuste se quiser
        if ($user->isSeller()) {
            return false;
        }

        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        if (! $user->can('orders.delete')) {
            return false;
        }

        return $user->isAdmin();
    }

    // Opcional (já deixa pronto)
    public function cancel(User $user, Order $order): bool
    {
        if (! $user->can('orders.cancel')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoop()) {
            return $user->hasAccessToGroup($order->group_id);
        }

        if ($user->isSeller()) {
            return $user->hasAccessToSeller($order->seller_id);
        }

        return false;
    }
}
