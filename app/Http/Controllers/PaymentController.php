<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use LiqPay;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Jobs\SendTelegramPaymentNotification;

class PaymentController extends Controller
{
    public function paypal(Order $order)
    {
        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            //$locale = app()->getLocale();

            $ngrokUrl = config('app.ngrok_url');
            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [[
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => '1.00',//number_format($order->total_price, 2, '.', ''),
                    ]
                ]],
                "application_context" => [
                    "cancel_url" => $ngrokUrl . route('paypal.cancel', $order, false),
                    "return_url" => $ngrokUrl . route('paypal.success', $order, false),
                    //"locale" => $this->mapLocaleToPayPal($locale),
                ]
            ]);

            Log::debug('PayPal createOrder response:', $response);

            if (isset($response['links'])) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
            }

            Log::error("PayPal error: no approve link. Order ID: {$order->id}");

        } catch (Exception $e) {
            Log::error("PayPal exception: " . $e->getMessage(), ['order_id' => $order->id]);
        }

        return redirect()->route('orders.show', $order)->with('error', 'Ошибка при оплате PayPal');
    }

    public function paypalSuccess(Request $request, Order $order)
    {
        Log::error("paypalSuccess: enter. Order ID: {$order->id}");
        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
    
            $orderId = $request->query('token');

            if (!$orderId) {
                Log::error("Missing PayPal order ID (token) in return URL", ['full_query' => $request->query()]);
                return redirect()->route('orders.show', $order)->with('error', 'PayPal не вернул ID заказа');
            }
            
            $response = $provider->capturePaymentOrder($orderId);
    
            Log::error("capturePaymentOrder response", ['response' => $response]);

            if (isset($response['error']['details'][0]['issue']) && $response['error']['details'][0]['issue'] === 'INSTRUMENT_DECLINED') {
                $redirectUrl = collect($response['error']['links'])->firstWhere('rel', 'redirect')['href'] ?? null;
            
                if ($redirectUrl) {
                    return redirect()->away($redirectUrl);
                }
            }

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                $order->update([
                    'status' => OrderStatus::Paid,
                    'response' => json_encode($response),
                    'payment_status' => '',
                ]);

                dispatch(new SendTelegramPaymentNotification($order));

                return redirect()->route('orders.show', $order)->with('success', 'Оплата через PayPal прошла успешно');
            } else {
                $order->update([
                    'status' => OrderStatus::Failed,
                    'response' => json_encode($response),
                    'payment_status' => $response['status'] ?? 'Неизвестная ошибка',
                ]);
                return redirect()->route('orders.show', $order)->with('error', 'Ошибка оплаты PayPal');
            }
        } catch (Exception $e) {
            Log::error('PayPal success handler error: '.$e->getMessage(), ['order_id' => $order->id]);
            return redirect()->route('orders.show', $order)->with('error', 'Ошибка при подтверждении оплаты через PayPal');
        }
    }
    
    public function paypalCancel(Order $order)
    {
        Log::error("paypalCancel: enter. Order ID: {$order->id}");
        $order->update(['status' => OrderStatus::Failed]);
        return redirect()->route('orders.show', $order)->with('error', 'Оплата PayPal была отменена');
    }
    
    private function mapLocaleToPayPal(string $locale): string
    {
        return match ($locale) {
            'en' => 'en_US',
            //'uk' => 'uk_UA',
            'ru' => 'ru_RU',
            'es' => 'es_ES',
            default => 'en_US',
        };
    }

    public function stripe(Order $order)
    {
        Log::info("stripe: enter. Order ID: {$order->id}");
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
    
            $ngrokUrl = config('app.ngrok_url');
    
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Order #{$order->id}"
                        ],
                        'unit_amount' => 100,//(int)($order->total_price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $ngrokUrl . route('stripe.success', $order, false),
                'cancel_url' => $ngrokUrl . route('stripe.cancel', $order, false),
            ]);
    
            return redirect($session->url);
        } catch (Exception $e) {
            Log::error('Stripe exception: ' . $e->getMessage(), ['order_id' => $order->id]);
            return redirect()->route('orders.show', $order)->with('error', 'Ошибка при оплате Stripe');
        }
    }
    
    public function stripeSuccess(Order $order)
    {
        Log::info("stripeSuccess: enter. Order ID: {$order->id}");
        if ($order->status !== OrderStatus::Paid) {
            $order->update([
                'status' => OrderStatus::Paid,
                'payment_status' => '',
                'response' => json_encode(['message' => 'Paid via Stripe Checkout']),
            ]);
        }
        
        dispatch(new SendTelegramPaymentNotification($order));

        return redirect()->route('orders.show', $order)->with('success', 'Оплата через Stripe прошла успешно');
    }
    
    public function stripeCancel(Order $order)
    {
        Log::info("stripeCancel: enter. Order ID: {$order->id}");
        $order->update(['status' => OrderStatus::Failed]);
        return redirect()->route('orders.show', $order)->with('error', 'Оплата Stripe была отменена');
    }

    public function liqpay(Order $order)
    {
        try {
            $liqpay = new LiqPay(config('services.liqpay.public_key'), config('services.liqpay.private_key'));
            $ngrokUrl = config('app.ngrok_url');
            
            $form = $liqpay->cnb_form([
                'action'         => 'pay',
                'sandbox'         => '1',
                'amount'         => '1.00',//number_format($order->total_price, 2, '.', ''),
                'currency'       => 'UAH',
                'description'    => "Order #{$order->id}",
                'order_id'       => $order->id,
                'version'        => '3',
                'result_url'   => route('liqpay.result', $order),
                'server_url'   => $ngrokUrl . route('liqpay.server', $order, false), // false — без базового APP_URL
            ]);

            $html = <<<HTML
            <!DOCTYPE html><body>
            {$form}<script>document.forms[0].submit();</script>
            </body></html>
            HTML;

            return response($html);
        } catch (Exception $e) {
            Log::error("LiqPay exception: " . $e->getMessage(), ['order_id' => $order->id]);
            return redirect()->route('orders.show', $order)->with('error', 'Ошибка при оплате LiqPay');
        }
    }

    public function liqpayResult(Request $request, Order $order)
    {
        if ($order->status == OrderStatus::Paid) {
            return redirect()->route('orders.show', $order)->with('success', 'Оплата через LiqPay прошла успешно');
        }
        else {
            return redirect()->route('orders.show', $order)->with('error', "Ошибка оплаты LiqPay: ".$order->payment_status);
        }
    }

    public function liqpayServer(Request $request, Order $order)
    {
        try {
            $data = $request->input('data');
            $signature = $request->input('signature');
    
            if (!$data || !$signature) {
                Log::warning('LiqPay callback missing data or signature', ['order_id' => $order->id]);
                return response('Missing data or signature', 400);
            }
    
            $decoded = base64_decode($data);
            $decodedData = json_decode($decoded, true);
    
            // Логируем всё, что пришло
            Log::info('LiqPay callback received', [
                'order_id' => $order->id,
                'status' => $decodedData['status'] ?? null,
                'err_code' => $decodedData['err_code'] ?? null,
                'err_description' => $decodedData['err_description'] ?? null,
                'response' => $decoded,
            ]);
    
            $status = $decodedData['status'] ?? null;
            if (in_array($status, ['success', 'sandbox'])) {
                $order->update([
                    'status' => OrderStatus::Paid,
                    'response' => $decoded,
                    'payment_status' => '',
                ]);

                dispatch(new SendTelegramPaymentNotification($order));

            } else {
                $order->update([
                    'status' => OrderStatus::Failed,
                    'response' => $decoded,
                    'payment_status' => $decodedData['err_code'].': '.$decodedData['err_description'],
                ]);
    
                Log::warning('LiqPay payment failed', [
                    'order_id' => $order->id,
                    'status' => $status,
                    'err_code' => $decodedData['err_code'] ?? null,
                    'err_description' => $decodedData['err_description'] ?? null,
                    'response' => $decoded,
                ]);
            }
    
            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('LiqPay server exception: ' . $e->getMessage(), ['order_id' => $order->id]);
            return response('Error', 500);
        }
    }
}