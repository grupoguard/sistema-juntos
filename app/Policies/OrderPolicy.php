<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return $user->can('orders.edit');
        }

        // COOP/SELLER só podem editar se estiver REJEITADO
        if (($user->isCoop() || $user->isSeller()) && $user->can('orders.edit')) {
            return $order->review_status === 'REJEITADO';
        }

        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.delete');
    }
}