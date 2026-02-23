<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class MenuHelper
{
    public static function canSeeItem(array $item, $user): bool
    {
        // Seção (título) só aparece se houver itens visíveis depois (vamos tratar no Blade)
        if (($item['type'] ?? null) === 'section') {
            return true;
        }

        // Link simples
        if (($item['type'] ?? null) === 'link') {
            return self::hasPermission($item, $user);
        }

        // Collapse: aparece se tiver pelo menos 1 filho visível
        if (($item['type'] ?? null) === 'collapse') {
            $children = $item['children'] ?? [];

            foreach ($children as $child) {
                if (self::hasPermission($child, $user)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    public static function hasPermission(array $item, $user): bool
    {
        $permission = $item['permission'] ?? null;
        $permissions = $item['permissions'] ?? null;

        if (!$permission && !$permissions) {
            return true;
        }

        if ($permission) {
            return $user->can($permission);
        }

        if (is_array($permissions) && count($permissions)) {
            foreach ($permissions as $perm) {
                if ($user->can($perm)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isActive(array $item): bool
    {
        $patterns = $item['active'] ?? [];

        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function collapseIsOpen(array $item): bool
    {
        foreach (($item['children'] ?? []) as $child) {
            if (self::isActive($child)) {
                return true;
            }
        }

        return false;
    }
}