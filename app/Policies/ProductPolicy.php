<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        // Permitir solo si el producto pertenece a la tienda del usuario
        return $user->store && $product->store_id === $user->store->id;
    }
}
