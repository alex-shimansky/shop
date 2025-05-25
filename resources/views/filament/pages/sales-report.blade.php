<x-filament::page>
    {{ $this->form }}

    @php
        $orders = $this->getOrders();
        $totalAmount = $orders->sum('total_price');
        $orderCount = $orders->count();
        $totalItemsSold = $orders->flatMap->orderItems->sum('quantity');

        $productStats = $orders
            ->flatMap->orderItems
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'name' => $product->name ?? '—',
                    'quantity' => $items->sum('quantity'),
                    'total' => $items->sum(fn($item) => $item->quantity * $item->price),
                ];
            })
            ->sortByDesc('quantity');
    @endphp

    <x-filament::card>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <div class="text-sm text-gray-500">Всего заказов:</div>
                <div class="text-xl font-semibold">{{ $orderCount }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Общая сумма:</div>
                <div class="text-xl font-semibold">{{ number_format($totalAmount, 2, ',', ' ') }} ₽</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Продано товаров:</div>
                <div class="text-xl font-semibold">{{ $totalItemsSold }}</div>
            </div>
        </div>

        <div class="mt-4">
            <div class="font-semibold mb-2">Проданные товары:</div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-1">Товар</th>
                        <th class="text-right py-1">Количество</th>
                        <th class="text-right py-1">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productStats as $product)
                        <tr class="border-b">
                            <td class="py-1">{{ $product['name'] }}</td>
                            <td class="text-right py-1">{{ $product['quantity'] }}</td>
                            <td class="text-right py-1">{{ number_format($product['total'], 2, ',', ' ') }} ₽</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::card>
</x-filament::page>