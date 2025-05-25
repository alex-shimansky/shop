<h1>Нове замовлення оформлено</h1>
<p>Замовник: {{ $order->name }} ({{ $order->phone }})</p>
<p>Адреса: {{ $order->shipping_address }}</p>
<p>Оплата: {{ $order->payment_method }}</p>
<p>Товари:</p>
<ul>
    @foreach ($order->orderItems as $item)
        <li>{{ $item->product->name }} — {{ $item->quantity }} × {{ $item->price }} грн</li>
    @endforeach
</ul>
<p>Сума: {{ $order->total_price }} грн</p>