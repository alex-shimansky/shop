<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('product_id')
                ->label('Товар')
                ->relationship('product', 'name->en') // Убедись, что поле name мультиязычное
                ->searchable()
                ->required(),

            TextInput::make('quantity')
                ->label('Количество')
                ->numeric()
                ->required(),

            TextInput::make('price')
                ->label('Цена')
                ->numeric()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Товар')->getStateUsing(
                fn ($record) => $record->product?->getTranslation('name', app()->getLocale())
            ),
            Tables\Columns\TextColumn::make('quantity')->label('Количество'),
            Tables\Columns\TextColumn::make('price')->label('Цена'),
        ]);
    }

    public static function afterCreate($record, $data): void
    {
        // Пересчитываем общую сумму после добавления товара
        static::updateOrderTotal($record->order);
    }
    
    public static function afterDelete($record): void
    {
        // Пересчитываем общую сумму после удаления товара
        static::updateOrderTotal($record->order);
    }
    
    protected static function updateOrderTotal($order)
    {
        // Пересчитываем сумму всех товаров
        $total = $order->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    
        // Обновляем сумму заказа
        $order->update(['total_price' => $total]);
    }
}