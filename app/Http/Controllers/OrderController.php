<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('orderItems.product')->where('user_id', Auth::id())->latest()->get();
    
        return view('orders.index', compact('orders'));
    }
    

    public function show_no_locale(Order $order)
    {
        return $this->show($order);
    }

    public function show_locale($locale = null, Order $order)
    {
        return $this->show($order);
    }

    private function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }
}
