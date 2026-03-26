<?php

namespace App\Services;

use App\Mail\SellerAccessMail;
use App\Models\Group;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserManagementService
{
    public function createUserForGroup(Group $group, bool $sendEmail = false)
    {
        return DB::transaction(function () use ($group, $sendEmail) {
            $generatedPassword = Str::random(12);

            $user = User::create([
                'name' => $group->name,
                'email' => $group->email,
                'password' => Hash::make($generatedPassword),
                'status' => true,
            ]);

            $user->assignRole('COOP');
            $this->assignGroupPermissions($user, $group);

            if ($sendEmail && !empty($user->email)) {
                Mail::to($user->email)->send(new SellerAccessMail(
                    name: $user->name,
                    email: $user->email,
                    password: $generatedPassword,
                    loginUrl: config('app.url') . '/login'
                ));
            }

            return [
                'user' => $user,
                'password' => $generatedPassword,
            ];
        });
    }

    public function createUserForSeller(Seller $seller, bool $sendEmail = false)
    {
        return DB::transaction(function () use ($seller) {
            $generatedPassword = Str::random(12);

            $user = User::create([
                'name' => $seller->name,
                'email' => $seller->email,
                'password' => Hash::make($generatedPassword),
                'status' => (bool) ($seller->status ?? true),
            ]);

            $user->assignRole('SELLER');

            $this->assignSellerPermissions($user, $seller);

            return [
                'user' => $user,
                'password' => $generatedPassword,
            ];
        });
    }

    private function assignGroupPermissions($user, $group)
    {
        $user->groups()->syncWithoutDetaching([$group->id]);
    }

    private function assignSellerPermissions($user, $seller)
    {
        $user->sellers()->syncWithoutDetaching([$seller->id]);
    }

    public function createUser(array $userData, array $accessData = [])
    {
        return DB::transaction(function () use ($userData, $accessData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'status' => $userData['status'] ?? true,
            ]);

            if (isset($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            return $user;
        });
    }

    public function assignUserAccess(User $user, array $accessData)
    {
        foreach ($accessData as $access) {
            UserAccess::create([
                'user_id' => $user->id,
                'group_id' => $access['group_id'],
                'userable_type' => $access['userable_type'],
                'role_id' => $access['role_id'],
            ]);
        }
    }

    public function updateUserAccess(User $user, array $newAccessData)
    {
        return DB::transaction(function () use ($user, $newAccessData) {
            $user->userAccesses()->delete();
            $this->assignUserAccess($user, $newAccessData);

            return $user->load(['userAccesses.role']);
        });
    }

    public function getAvailableEntities()
    {
        return [
            'App\Models\Admin' => \App\Models\Admin::all(),
            'App\Models\Seller' => \App\Models\Seller::all(),
            'App\Models\Partner' => \App\Models\Partner::all(),
        ];
    }

    public function getUsersByRole($roleName)
    {
        return User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->with(['userAccesses.role'])->get();
    }
}