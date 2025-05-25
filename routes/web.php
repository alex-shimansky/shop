<?php

use Illuminate\Support\Facades\Route;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\FirstpageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/delete', [CartController::class, 'delete'])->name('cart.delete');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->middleware('auth')->name('cart.checkout');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

Route::middleware('auth')->get('/orders/{order}', [OrderController::class, 'show_no_locale'])->name('orders.show');
Route::middleware('auth')->get('/orders', [OrderController::class, 'index'])->name('orders.index');

Route::get('/', [FirstpageController::class, 'index'])->name('firstpage.index');
Route::get('/categories/{category}', [CategoryController::class, 'index_no_locale'])->name('categories.index');

Route::get('/payment/paypal/{order}', [PaymentController::class, 'paypal'])->name('payment.paypal');
Route::get('/payment/stripe/{order}', [PaymentController::class, 'stripe'])->name('payment.stripe');
Route::get('/payment/liqpay/{order}', [PaymentController::class, 'liqpay'])->name('payment.liqpay');

Route::get('/payment/paypal/success/{order}', [PaymentController::class, 'paypalSuccess'])->name('paypal.success');
Route::get('/payment/paypal/cancel/{order}', [PaymentController::class, 'paypalCancel'])->name('paypal.cancel');

Route::get('payment/stripe/success/{order}', [PaymentController::class, 'stripeSuccess'])->name('stripe.success');
Route::get('payment/stripe/cancel/{order}', [PaymentController::class, 'stripeCancel'])->name('stripe.cancel');

Route::get('payment/liqpay/result/{order}', [PaymentController::class, 'liqpayResult'])->name('liqpay.result');
Route::post('/payment/liqpay/server/{order}', [PaymentController::class, 'liqpayServer'])->name('liqpay.server');


Route::group([
    'prefix' => '{locale?}',
//    'where' => ['locale' => 'en|es'],
    'middleware' => ['set.app.locale'],
], function () {
    Route::get('/', [FirstpageController::class, 'index'])->name('firstpage.index');
    Route::get('/categories/{category}', [CategoryController::class, 'index_locale'])->name('categories.index');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/delete', [CartController::class, 'delete'])->name('cart.delete');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->middleware('auth')->name('cart.checkout');
    
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

    //Route::middleware('auth')->get('/orders/{order}', [OrderController::class, 'show_locale'])->name('orders.show');
    //Route::middleware('auth')->get('/orders', [OrderController::class, 'index'])->name('orders.index');
});




Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


Route::get('auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'callback']);