<?php

namespace App\Filament\Resources\CartResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Товар'),
                TextColumn::make('quantity')->label('Кол-во'),
                TextColumn::make('product.price')->label('Цена за ед.'),
                TextColumn::make('custom_total')
                ->label('Сумма')
                ->state(function ($record) {
                    return $record->product ? $record->product->price * $record->quantity : null;
                })
                ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' ₴'),
            ]);
    }
}