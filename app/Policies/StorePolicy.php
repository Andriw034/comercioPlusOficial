<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function view(User $user, Store $store)
    {
        return $user->id === $store->user_id;
    }

    public function update(User $user, Store $store)
    {
        return $user->id === $store->user_id;
    }

    public function delete(User $user, Store $store)
    {
        return $user->id === $store->user_id;
    }
}
