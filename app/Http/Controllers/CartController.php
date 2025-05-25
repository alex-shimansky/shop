<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

use App\Mail\OrderConfirmationMail;
use App\Mail\NewOrderNotificationMail;
use Illuminate\Support\Facades\Mail;


class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();

        $items = $products->map(function ($product) use ($cart) {
            return [
                'product' => $product,
                'quantity' => $cart[$product->id]['quantity'],
                'subtotal' => $product->price * $cart[$product->id]['quantity'],
            ];
        });

        $total = $items->sum('subtotal');

        return view('cart.index', compact('items', 'total'));
    }

    public function update(Request $request)
    {
        $cart = session('cart', []);
        $cart[$request->product_id]['quantity'] = (int) $request->quantity;
        session(['cart' => $cart]);

        if (Auth::check()) $this->updateCartItem($request->product_id, $cart[$request->product_id]['quantity']);

        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $cart = session('cart', []);
        unset($cart[$request->product_id]);
        session(['cart' => $cart]);

        if (Auth::check()) $this->removeCartItem($request->product_id);

        return response()->json(['success' => true]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:1000',
            'payment_method' => 'required',
        ]);
    
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }
    
        $products = Product::whereIn('id', array_keys($cart))->get();
    
        $order = Order::create([
            'user_id' => Auth::id(),
            'status' => OrderStatus::Pending,
            'total_price' => 0,
            'shipping_address' => $request->shipping_address,
            'payment_method' => $request->payment_method,
            'name' => $request->name,
            'phone' => $request->phone,
        ]);
    
        foreach ($products as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $cart[$product->id]['quantity'],
            ]);
        }
    
        $order->recalculateTotalPrice();


        session()->forget('cart');

        if (Auth::check()) $this->placeOrder();

        // Отправка пользователю
        if (Auth::user()->email) {
            Mail::to(Auth::user()->email)->send(new OrderConfirmationMail($order));
        }

        // Уведомление администрации
        Mail::to('shimansky77@gmail.com')->send(new NewOrderNotificationMail($order));
        
        switch ($request->payment_method) {
            case PaymentMethod::Paypal->value:
                return redirect()->route('payment.paypal', $order);
            case PaymentMethod::Stripe->value:
                return redirect()->route('payment.stripe', $order);
            case PaymentMethod::Liqpay->value:
                return redirect()->route('payment.liqpay', $order);
            default:
                return redirect()->route('orders.show', $order)->with('success', __('messages.order_success'));
        }
    }

    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $cart = session('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = ['quantity' => 1];
        }
        session(['cart' => $cart]);
    
        if (Auth::check()) $this->addToCart($productId, $cart[$productId]['quantity']);

        return back()->with('success', 'Товар добавлен в корзину!');
    }

    public function addToCart(int $product_id, int $quantity = 1): void
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
    
        $item = $cart->items()->where('product_id', $product_id)->first();
    
        if ($item) {
            $item->increment('quantity', 1);
        } else {
            $cart->items()->create([
                'product_id' => $product_id,
                'quantity' => $quantity,
            ]);
        }
    }

    public function updateCartItem(int $product_id, int $quantity): void
    {
        $item = CartItem::whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('product_id', $product_id)
            ->firstOrFail();
    
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }
    }

    public function removeCartItem(int $product_id): void
    {
        CartItem::whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('product_id', $product_id)
            ->delete();
    }

    public function placeOrder()
    {
        $cart = Cart::with('items.product')->where('user_id', Auth::id())->firstOrFail();
    
        $cart->items()->delete();
        $cart->delete();
    }
}