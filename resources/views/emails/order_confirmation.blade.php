<h1>Дякуємо за Ваше замовлення!</h1>
<p>Номер замовлення: {{ $order->id }}</p>
<p>Адреса доставки: {{ $order->shipping_address }}</p>
<p>Оплата: {{ $order->payment_method }}</p>
<p>Товари:</p>
<ul>
    @foreach ($order->orderItems as $item)
        <li>{{ $item->product->name }} — {{ $item->quantity }} × {{ $item->price }} грн</li>
    @endforeach
</ul>
<p>Загальна сума: {{ $order->total_price }} грн</p>