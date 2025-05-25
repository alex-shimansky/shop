<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Mail\OrderConfirmationMail;
use App\Mail\NewOrderNotificationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_product_to_cart()
    {
        $category = Category::create(['name' => ['en' => 'Test'], 'description' => ['en' => 'desc']]);

        $product = Product::create([
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Test Description'],
            'price' => 100,
            'quantity' => 10,
            'image' => 'test.jpg',
            'category_id' => $category->id,
        ]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $this->assertArrayHasKey($product->id, session('cart'));
    }

    public function test_update_cart_item_quantity()
    {
        $category = Category::create(['name' => ['en' => 'Test'], 'description' => ['en' => 'desc']]);

        $product = Product::create([
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Test Description'],
            'price' => 100,
            'quantity' => 10,
            'image' => 'test.jpg',
            'category_id' => $category->id,
        ]);
        
        $this->withSession(['cart' => [$product->id => ['quantity' => 1]]]);

        $response = $this->post(route('cart.update'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals(3, session('cart')[$product->id]['quantity']);
    }

    public function test_delete_product_from_cart()
    {
        $category = Category::create(['name' => ['en' => 'Test'], 'description' => ['en' => 'desc']]);

        $product = Product::create([
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Test Description'],
            'price' => 100,
            'quantity' => 10,
            'image' => 'test.jpg',
            'category_id' => $category->id,
        ]);

        $this->withSession(['cart' => [$product->id => ['quantity' => 2]]]);

        $response = $this->post(route('cart.delete'), [
            'product_id' => $product->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertArrayNotHasKey($product->id, session('cart'));
    }

    public function test_checkout_creates_order_and_redirects_to_payment()
    {
        Mail::fake();
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $category = Category::create(['name' => ['en' => 'Test'], 'description' => ['en' => 'desc']]);

        $product = Product::create([
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Test Description'],
            'price' => 100,
            'quantity' => 10,
            'image' => 'test.jpg',
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)->withSession(['cart' => [$product->id => ['quantity' => 2]]]);

        $response = $this->post(route('cart.checkout'), [
            'name' => 'Test User',
            'phone' => '123456789',
            'shipping_address' => 'Test Street 123',
            'payment_method' => PaymentMethod::Stripe->value,
        ]);

        //$response->dump(); // выводит весь ответ

        //$response->assertStatus(302); // статус — должен быть redirect
        //$response->assertRedirect(); // должен быть redirect вообще
        
        //$response->assertSessionHasNoErrors(); // нет ошибок валидации

// $response->assertRedirect(); // Убедиться, что вообще был редирект
// $redirectUrl = $response->headers->get('Location');
// $this->assertEquals(route('payment.stripe', $order), $redirectUrl);

        $order = Order::first();
        $response->assertRedirect(route('payment.stripe', $order));
        $this->assertEquals(200, $order->total_price);
        $this->assertDatabaseCount('order_items', 1);

        Mail::assertQueued(OrderConfirmationMail::class);
        Mail::assertQueued(NewOrderNotificationMail::class);
    }
}
