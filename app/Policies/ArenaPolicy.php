<?php

namespace App\Policies;

use App\Models\Arena;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArenaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view arenas (their own and public ones)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Arena $arena): bool
    {
        return $user->id === $arena->user_id || $arena->is_public;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create arenas
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Arena $arena): bool
    {
        return $user->id === $arena->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Arena $arena): bool
    {
        return $user->id === $arena->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Arena $arena): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Arena $arena): bool
    {
        return false;
    }
}
