@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('messages.my_orders') }}</h1>

        @forelse($orders as $order)
            <div class="border p-4 rounded-lg mb-4 shadow">
                <div class="mb-2">
                    <strong>{{ __('messages.order') }} #{{ $order->id }}</strong> — {{ $order->created_at->format('d.m.Y') }}
                </div>
                <div class="mb-2">
                    {{ __('messages.status') }}: {{ $order->status->name }} <br>
                    {{ __('messages.payment_type') }}:
                    {{ match(\App\Enums\PaymentMethod::from($order->payment_method)) {
                    \App\Enums\PaymentMethod::Cash => __('messages.payment_cash'),
                    \App\Enums\PaymentMethod::Cod => __('messages.payment_cod'),
                    \App\Enums\PaymentMethod::Card => __('messages.payment_card'),
                    \App\Enums\PaymentMethod::Paypal => __('messages.payment_paypal'),
                    \App\Enums\PaymentMethod::Stripe => __('messages.payment_stripe'),
                    \App\Enums\PaymentMethod::Liqpay => __('messages.payment_liqpay'),
                } }}
                </div>
                <div class="mb-2">
                    {{ __('messages.total') }}: {{ $order->total_price }} {{ __('messages.hrn') }}
                </div>
                <div class="mb-2">
                    <a href="{{ route('orders.show', $order->id) }}" class="text-blue-500 hover:underline">
                        {{ __('messages.view') ?? 'Просмотр' }}
                    </a>
                </div>

                <div class="mt-2">
                    <strong>{{ __('messages.items') }}:</strong>
                    <ul class="list-disc pl-5">
                        @foreach($order->orderItems as $item)
                            <li>
                                {{ $item->product->name ?? 'Удалённый товар' }} × {{ $item->quantity }} — {{ $item->price }} {{ __('messages.hrn') }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <p>{{ __('messages.orders_empty') ?? 'У вас пока нет заказов.' }}</p>
        @endforelse
    </div>
@endsection
