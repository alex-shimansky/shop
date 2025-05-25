<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';               // Ожидает обработки
    case Processing = 'processing';         // Подтверждён, собирается
    case Paid = 'paid';                     // Оплачен
    case Failed = 'failed';                     // Оплачен
    case Shipped = 'shipped';               // Отправлен
    case Delivered = 'delivered';           // Доставлен
    case Cancelled = 'cancelled';           // Отменён
    case Returned = 'returned';             // Возврат
}