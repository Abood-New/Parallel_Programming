<div>
    <h1>Order Confirmation</h1>

    <p>Thank you for your order!</p>

    <p>Order ID: {{ $order->id }}</p>
    <p>Total: ${{ $order->total }}</p>

    <h3>Items:</h3>
    <ul>
        @foreach($order->items as $item)
            <li>
                {{ $item->product->name }} × {{ $item->quantity }}
            </li>
        @endforeach
    </ul>
</div>