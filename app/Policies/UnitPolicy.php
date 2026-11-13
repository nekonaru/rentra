<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function view(User $user, Unit $unit): bool
    {
        return $user->rental?->id === $unit->rental_id;
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->rental?->id === $unit->rental_id;
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->rental?->id === $unit->rental_id;
    }
}
