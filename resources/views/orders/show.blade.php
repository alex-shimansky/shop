@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <h1 class="text-2xl font-bold mb-4">{{ __('messages.order') }} #{{ $order->id }}</h1>

        <p><strong>{{ __('messages.date') }}:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>

        <div class="mb-4">
            <p><strong>{{ __('messages.status') }}:</strong> {{ $order->status->name }}</p>
            <p><strong>{{ __('messages.address') }}:</strong> {{ $order->shipping_address }}</p>
            <p><strong>{{ __('messages.payment_type') }}:</strong>
                {{ match(\App\Enums\PaymentMethod::from($order->payment_method)) {
                    \App\Enums\PaymentMethod::Cash => __('messages.payment_cash'),
                    \App\Enums\PaymentMethod::Cod => __('messages.payment_cod'),
                    \App\Enums\PaymentMethod::Card => __('messages.payment_card'),
                    \App\Enums\PaymentMethod::Paypal => __('messages.payment_paypal'),
                    \App\Enums\PaymentMethod::Stripe => __('messages.payment_stripe'),
                    \App\Enums\PaymentMethod::Liqpay => __('messages.payment_liqpay'),
                } }}
            </p>
            <p><strong>{{ __('messages.name') }}:</strong> {{ $order->name }}</p>
            <p><strong>{{ __('messages.phone') }}:</strong> {{ $order->phone }}</p>
        </div>

        <h2 class="text-xl font-semibold mb-2">{{ __('messages.items') }}</h2>

        <div class="space-y-4">
            @foreach($order->orderItems as $item)
                <div class="border p-4 rounded bg-white shadow-sm">
                    <p class="font-semibold">{{ $item->product->name }}</p>
                    <p>{{ __('messages.price') }}: {{ $item->price }} грн</p>
                    <p>{{ __('messages.quantity') }}: {{ $item->quantity }}</p>
                    <p>{{ __('messages.total') }}: {{ $item->price * $item->quantity }} грн</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 text-right text-lg font-bold">
            {{ __('messages.total') }}: {{ $order->total_price }} грн
        </div>
    </div>
@endsection