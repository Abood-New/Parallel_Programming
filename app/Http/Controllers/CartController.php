<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cart = $this->cartService->getCart($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = $this->cartService->addToCart(
            $request->user(),
            $validated['product_id'],
            $validated['quantity']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'data' => $cart
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer'
        ]);

        $cart = $this->cartService->updateItem(
            $request->user(),
            $id,
            $validated['quantity']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated',
            'data' => $cart
        ]);
    }

    public function remove(Request $request, $id)
    {
        $cart = $this->cartService->removeItem(
            $request->user(),
            $id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed',
            'data' => $cart
        ]);
    }

    public function clear(Request $request)
    {
        $this->cartService->clearCart($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared'
        ]);
    }
}
