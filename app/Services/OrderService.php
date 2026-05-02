<?php

namespace App\Services;

use App\Aspects\LoggingAspect;
use App\Aspects\NotificationAspect;
use App\Aspects\TransactionAspect;
use App\Jobs\SendOrderEmailJob;
use App\Services\CartService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;

class OrderService
{
    protected $cartService;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Checkout process
     */
    public function checkout($user, $mode = 'safe')
    {
        if ($mode == 'unsafe') {
            LoggingAspect::unsafeLog("=== STARTING UNSAFE CHECKOUT {$user->id} AT " . microtime(true) . " ===");
        } else {
            LoggingAspect::safeLog("=== STARTING SAFE CHECKOUT {$user->id} AT " . microtime(true) . " ===");
        }
        return TransactionAspect::handle(fn() =>
            $mode == 'unsafe' ? $this->processOrderUnsafe($user) : $this->processOrderSafe($user));
    }
    private function processOrderUnsafe($user)
    {
        $cart = $this->cartService->getCart($user);

        if ($cart->items->isEmpty()) {
            throw new Exception("Cart is empty");
        }

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 0,
            'status' => 'pending',
        ]);

        $total = 0;

        foreach ($cart->items as $item) {
            $this->processItemUnsafe($item, $order, $total);
        }

        $order->update(['total' => $total]);

        $this->processPayment($order);
        $this->cartService->clearCart($user);

        return $order->load('items.product');
    }
    private function processOrderSafe($user)
    {
        $cart = $this->cartService->getCart($user);

        if ($cart->items->isEmpty()) {
            throw new Exception("Cart is empty");
        }

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 0,
            'status' => 'pending',
        ]);

        $total = 0;

        foreach ($cart->items as $item) {
            $total = $this->processItem($item, $order, $total);
        }
        

        // $order->update(['total' => $total]);

        $order->total = $total;
        $order->save();
        
        $this->processPayment($order);

        $this->cartService->clearCart($user);

        NotificationAspect::afterOrderCreated($order);

        return $order->load('items.product');
    }
    private function processItemUnsafe($item, $order, &$total)
    {
        $product = Product::find($item->product_id);

        // ❌ read without lock
        $stock = $product->stock;

        LoggingAspect::unsafeLog("[" . auth()->user()->id . "] READ stock={$stock} AT " . microtime(true));

        // sleep(3); // force race condition

        if ($stock < $item->quantity) {
            LoggingAspect::unsafeLog("[" . auth()->user()->id . "] FAILED (out of stock) AT " . microtime(true));
            throw new Exception("Product out of stock");
        }

        // ❌ write using stale value
        $product->stock = $stock - $item->quantity;
        $product->save();

        LoggingAspect::unsafeLog("[" . auth()->user()->id . "] WRITE stock={$product->stock} AT " . microtime(true));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $item->quantity,
            'price' => $product->price,
        ]);

        $total += $product->price * $item->quantity;
    }
    private function processItem($item, $order, &$total)
    {
        $product = $this->lockAndFetchProduct($item->product_id);

        $this->validateStock($product, $item);

        $this->updateStock($product, $item);

        $this->createOrderItem($product, $item, $order);

        $total += $product->price * $item->quantity;
        return $total;
    }
    private function lockAndFetchProduct($productId)
    {
        $product = Product::where('id', $productId)
            ->lockForUpdate()
            ->first();

        LoggingAspect::safeLog("[" . auth()->user()->id . "] LOCK AQUIRED stock={$product->stock} AT " . microtime(true));

        // sleep(3); // Simulate long processing

        return $product;
    }
    private function validateStock($product, $item)
    {
        if ($product->stock < $item->quantity) {
            LoggingAspect::safeLog("[" . auth()->user()->id . "] FAILED (out of stock) AT " . microtime(true));
            throw new Exception("Product {$product->name} out of stock");
        }
    }
    private function updateStock($product, $item)
    {
        $product->decrement('stock', $item->quantity);
        LoggingAspect::safeLog("[" . auth()->user()->id . "] UPDATED stock={$product->stock} AT " . microtime(true));
    }
    private function createOrderItem($product, $item, $order)
    {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $item->quantity,
            'price' => $product->price,
        ]);
    }

    /**
     * Simulated payment
     */
    protected function processPayment($order)
    {
        $success = true;

        if (!$success) {
            throw new Exception("Payment failed");
        }

        $order->update([
            'status' => 'paid'
        ]);
    }
}
