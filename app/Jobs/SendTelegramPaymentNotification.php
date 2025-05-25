<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegram): void
    {
        $message = "💳 *Оплата заказа #{$this->order->id}*\n"
                 . "Сумма: *{$this->order->total_price}*\n"
                 . "Статус: *" . ($this->order->status->value ?? 'unknown') . "*";

        $telegram->sendMessage($message);
    }
}
