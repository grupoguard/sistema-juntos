<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        // Todos os perfis do sistema podem ver a listagem SE tiverem a permissão.
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        // Só admin (ou quem você der permissão) consegue criar
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        // Só admin (ou quem você der permissão) consegue editar
        return $user->can('products.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        // Só admin (ou quem você der permissão) consegue excluir
        return $user->can('products.delete');
    }
}