<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-m-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->relationship('user', 'name')
                ->label('Пользователь')
                ->required(),

            Select::make('status')
                ->label('Статус заказа')
                ->options([
                    OrderStatus::Pending->value => 'Ожидает',
                    OrderStatus::Processing->value => 'В обработке',
                    OrderStatus::Shipped->value => 'Отправлен',
                    OrderStatus::Delivered->value => 'Доставлен',
                    OrderStatus::Cancelled->value => 'Отменён',
                    OrderStatus::Returned->value => 'Возврат',
                ])
                ->required(),

            TextInput::make('shipping_address')
                ->label('Адрес доставки')
                ->required(),
            
            Select::make('payment_method')
                ->label('Способ оплаты')
                ->options([
                    PaymentMethod::Cash->value => 'Наличные',
                    PaymentMethod::Cod->value => 'Наложенный платёж',
                    PaymentMethod::Card->value => 'Картой',
                    PaymentMethod::Paypal->value => 'Paypal',
                    PaymentMethod::Stripe->value => 'Stripe',
                    PaymentMethod::Liqpay->value => 'Liqpay',
                ])
                ->required(),

            TextInput::make('name')
                ->label('Имя')
                ->required(),

            TextInput::make('phone')
                ->label('Телефон')
                ->required(),

            Repeater::make('orderItems') // связь с методом orderItems() в Order
                ->relationship()
                ->label('Товары')
                ->schema([
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->label('Товар')
                        ->required(),

                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    TextInput::make('price')
                        ->label('Цена')
                        ->numeric()
                        ->required(),
                ])
                ->createItemButtonLabel('Добавить товар'),

            TextInput::make('total_price')
                ->label('Сумма')
                ->numeric()
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id'),
            TextColumn::make('user.name')->label('Пользователь'),
            TextColumn::make('status')->label('Статус'),
            TextColumn::make('total_price')->label('Сумма'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}