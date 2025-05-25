<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';               // Наличные
    case Cod = 'cod';                 // Наложенный платёж
    case Card = 'card';               // Картой
    case Paypal = 'paypal';           // Доставлен
    case Stripe = 'stripe';           // Отменён
    case Liqpay = 'liqpay';            // Возврат
}