<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        $userData = [
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if (isset($data['avatar'])) {
            $userData['avatar'] = $this->uploadAvatar($data['avatar']);
        }

        $user = User::create($userData);

        // Assign user to stores if provided
        if (isset($data['stores']) && !empty($data['stores'])) {
            $user->stores()->sync($data['stores']);
        }

        return $user;
    }

    /**
     * Update an existing user
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $userData = [
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? $user->phone,
            'address' => $data['address'] ?? $user->address,
            'role' => $data['role'] ?? $user->role,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ];

        if (isset($data['password']) && !empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        if (isset($data['avatar'])) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userData['avatar'] = $this->uploadAvatar($data['avatar']);
        }

        $user->update($userData);

        // Update user store assignments if provided
        if (isset($data['stores'])) {
            $user->stores()->sync($data['stores']);
        }

        return $user;
    }

    /**
     * Delete a user
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        return $user->delete();
    }

    /**
     * Upload user avatar
     *
     * @param $avatar
     * @return string
     */
    private function uploadAvatar($avatar): string
    {
        $fileName = 'user_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
        $path = $avatar->storeAs('users/avatars', $fileName, 'public');
        return $path;
    }

    /**
     * Toggle user active status
     *
     * @param User $user
     * @return User
     */
    public function toggleActiveStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user;
    }

    /**
     * Get users by role
     *
     * @param int $tenantId
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByRole(int $tenantId, string $role)
    {
        return User::where('tenant_id', $tenantId)
            ->where('role', $role)
            ->where('is_active', true)
            ->get();
    }
}
