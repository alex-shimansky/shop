@extends('layouts.app')

@section('content')
<main class="py-10 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">{{ __('messages.basket') }}</h1>

        @if ($items->isEmpty())
            <p>{{ __('messages.basket_empty') }}</p>
        @else
        <table class="w-full bg-white border shadow mb-6">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">{{ __('messages.product') }}</th>
                    <th class="p-3 text-center">{{ __('messages.quantity') }}</th>
                    <th class="p-3 text-right">{{ __('messages.price') }}</th>
                    <th class="p-3 text-right">{{ __('messages.subtotal') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr data-id="{{ $item['product']->id }}">
                    <td class="p-3">{{ $item['product']->name }}</td>
                    <td class="p-3 text-center">
                        <input type="number" class="w-16 text-center border rounded quantity-input" value="{{ $item['quantity'] }}" min="1">
                    </td>
                    <td class="p-3 text-right">{{ $item['product']->price }} {{ __('messages.hrn') }}</td>
                    <td class="p-3 text-right subtotal">{{ $item['subtotal'] }} {{ __('messages.hrn') }}</td>
                    <td class="p-3 text-center">
                        <button class="delete-btn text-red-500 hover:underline">{{ __('messages.delete') }}</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-between items-center">
            <div class="text-xl font-bold">{{ __('messages.total') }}: <span id="total">{{ $total }}</span> {{ __('messages.hrn') }}</div>
        </div>

        <form method="POST" action="{{ route('cart.checkout') }}" class="w-full bg-white border mt-6 p-6 shadow rounded space-y-4">
                @csrf

                <div>
                    <label class="block mb-1 text-gray-700 dark:text-gray-300">{{ __('messages.name') }}</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border rounded" value="{{ old('name') }}">
                    @error('name')<div class="text-red-600">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block mb-1 text-gray-700 dark:text-gray-300">{{ __('messages.phone') }}</label>
                    <input type="text" name="phone" class="w-full px-4 py-2 border rounded" value="{{ old('phone') }}">
                    @error('phone')<div class="text-red-600">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.address') }}</label>
                    <textarea name="shipping_address" rows="3" required class="w-full border rounded p-2">{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')<div class="text-red-600">{{ $message }}</div>@enderror
                </div>

                @php
                    $paymentOptions = \App\Enums\PaymentMethod::cases();
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.payment_type') }}</label>
                    <select name="payment_method" required class="w-full border rounded p-2">
                        <option value="">{{ __('messages.payment_choose') }}</option>
                    @foreach ($paymentOptions as $method)
                        <option value="{{ $method->value }}" {{ old('payment_method') == $method->value ? 'selected' : '' }}>
                            {{ match($method) {
                                \App\Enums\PaymentMethod::Cash => __('messages.payment_cash'),
                                \App\Enums\PaymentMethod::Cod => __('messages.payment_cod'),
                                \App\Enums\PaymentMethod::Card => __('messages.payment_card'),
                                \App\Enums\PaymentMethod::Paypal => __('messages.payment_paypal'),
                                \App\Enums\PaymentMethod::Stripe => __('messages.payment_stripe'),
                                \App\Enums\PaymentMethod::Liqpay => __('messages.payment_liqpay'),
                            } }}
                        </option>
                    @endforeach
                    </select>
                </div>

                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                {{ __('messages.checkout') }}
                </button>
            </form>
        @endif
    </div>
</main>

<script>
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function () {
            const row = this.closest('tr');
            const id = row.dataset.id;
            const quantity = this.value;

            fetch('{{ route('cart.update') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: id, quantity })
            }).then(() => {
                const price = parseFloat(row.children[2].textContent);
                const subtotal = price * quantity;
                row.querySelector('.subtotal').textContent = subtotal.toFixed(2) + ' {{ __('messages.hrn') }}';

                let total = 0;
                document.querySelectorAll('.subtotal').forEach(el => {
                    total += parseFloat(el.textContent);
                });
                document.getElementById('total').textContent = total.toFixed(2);
            });
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const id = row.dataset.id;

            fetch('{{ route('cart.delete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: id })
            }).then(() => {
                row.remove();
                let total = 0;
                document.querySelectorAll('.subtotal').forEach(el => {
                    total += parseFloat(el.textContent);
                });
                document.getElementById('total').textContent = total.toFixed(2);
            });
        });
    });
</script>
@endsection