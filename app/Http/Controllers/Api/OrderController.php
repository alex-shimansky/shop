<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use OpenApi\Annotations as OA;

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user and return API token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abc123def456...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        $token = $user->createToken('api-token')->plainTextToken;
    
        return response()->json(['token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abc123def456...")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout the authenticated user",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }


    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Получить список заказов текущего пользователя",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     )
     * )
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->orders()->with('orderItems.product')->get();
    }

        /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Создать заказ",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_address","payment_method","name","phone","order_items"},
     *             @OA\Property(property="shipping_address", type="string"),
     *             @OA\Property(property="payment_method", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(
     *                 property="order_items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_id","quantity","price"},
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="price", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заказ создан",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|integer|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.price' => 'required|numeric|min:0',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $order = $user->orders()->create([
            'shipping_address' => $data['shipping_address'],
            'payment_method' => $data['payment_method'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'status' => 'pending',
            'total_price' => collect($data['order_items'])->sum(fn($item) => $item['price'] * $item['quantity']),
        ]);

        foreach ($data['order_items'] as $item) {
            $order->orderItems()->create($item);
        }

        return response()->json($order->load('orderItems'), 201);
    }

        /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Получить один заказ",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Детали заказа",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет доступа"
     *     )
     * )
     */
    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        return $order->load('orderItems.product');
    }

        /**
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     summary="Обновить заказ",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="shipping_address", type="string"),
     *             @OA\Property(property="payment_method", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(
     *                 property="order_items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_id","quantity","price"},
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="price", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Обновлённый заказ",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     )
     * )
     */
    public function update(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $data = $request->validate([
            'shipping_address' => 'sometimes|string',
            'payment_method' => 'sometimes|string',
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'status' => 'sometimes|string',
            'order_items' => 'sometimes|array',
            'order_items.*.product_id' => 'required_with:order_items|integer|exists:products,id',
            'order_items.*.quantity' => 'required_with:order_items|integer|min:1',
            'order_items.*.price' => 'required_with:order_items|numeric|min:0',
        ]);

        $order->update($data);

        if (isset($data['order_items'])) {
            $order->orderItems()->delete();
            foreach ($data['order_items'] as $item) {
                $order->orderItems()->create($item);
            }
            $order->update([
                'total_price' => collect($data['order_items'])->sum(fn($item) => $item['price'] * $item['quantity']),
            ]);
        }

        return $order->load('orderItems.product');
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Удалить заказ",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заказ удалён",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order deleted")
     *         )
     *     )
     * )
     */
    public function destroy(Order $order)
    {
        $this->authorizeOrder($order);
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    protected function authorizeOrder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
    }
}