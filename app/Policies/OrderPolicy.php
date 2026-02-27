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
        if (! $user->can('orders.view')) return false;

        if ($user->isAdmin()) return true;

        // Mesma lógica do scopeVisibleTo, só que no nível do recurso
        if ($user->isCoop()) {
            $groupIds = $user->getAccessibleGroupIds();
            return !empty($groupIds) && in_array((int) $order->group_id, array_map('intval', $groupIds), true);
        }

        if ($user->isSeller()) {
            $sellerIds = $user->getAccessibleSellerIds();
            return !empty($sellerIds) && in_array((int) $order->seller_id, array_map('intval', $sellerIds), true);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        // precisa ao menos poder ver o pedido
        if (! $this->view($user, $order)) return false;

        if (! $user->can('orders.edit')) return false;

        if ($user->isAdmin()) return true;

        // COOP/SELLER: só edita se REPROVADO
        if ($user->isCoop() || $user->isSeller()) {
            return $order->review_status === 'REPROVADO';
        }

        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        if (! $this->view($user, $order)) return false;

        return $user->can('orders.delete');
    }
}