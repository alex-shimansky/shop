<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Cart;
use App\Models\CartItem;

class MergeCartAfterLogin
{
    public function handle(Login $event)
    {
        $sessionCart = session('cart', []);
        if (empty($sessionCart)) return;

        $user = $event->user;

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($sessionCart as $productId => $data) {
            $existingItem = $cart->items()->where('product_id', $productId)->first();

            if ($existingItem) {
                // Сложить количества
                $existingItem->increment('quantity', $data['quantity']);
            } else {
                // Добавить новый товар
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $data['quantity'],
                ]);
            }
        }

        // Очистить сессию после переноса
        //session()->forget('cart');
    }
}