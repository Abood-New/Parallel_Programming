<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartService
{
    /**
     * Get or create cart for user
     */
    public function getUserCart($user)
    {
        return Cart::firstOrCreate([
            'user_id' => $user->id
        ]);
    }

    /**
     * Add item to cart
     */
    public function addToCart($user, $productId, $quantity)
    {
        $cart = $this->getUserCart($user);

        $product = Product::findOrFail($productId);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $cart->load('items.product');
    }

    /**
     * Update item quantity
     */
    public function updateItem($user, $itemId, $quantity)
    {
        $cart = $this->getUserCart($user);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        return $cart->load('items.product');
    }

    /**
     * Remove item
     */
    public function removeItem($user, $itemId)
    {
        $cart = $this->getUserCart($user);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->delete();

        return $cart->load('items.product');
    }

    /**
     * Clear cart
     */
    public function clearCart($user)
    {
        $cart = $this->getUserCart($user);

        $cart->items()->delete();

        return true;
    }

    /**
     * Get full cart
     */
    public function getCart($user)
    {
        $cart = $this->getUserCart($user);

        return $cart->load('items.product');
    }
}
