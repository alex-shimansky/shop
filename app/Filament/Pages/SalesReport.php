<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;

class SalesReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.sales-report';
    protected static ?string $title = 'Отчёт по продажам';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Order::query()->with('user'))
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('user.name')->label('Пользователь'),
                TextColumn::make('status')->label('Статус'),
                TextColumn::make('total_price')->label('Сумма'),
                TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i'),
            ]);
    }

    public function getOrders()
    {
        return Order::with('user')->latest()->get();
    }

    public function getStats(): array
    {
        $orders = Order::query()
            ->whereDate('created_at', '>=', Carbon::now()->subMonth())
            ->get();

        return [
            'count' => $orders->count(),
            'total' => $orders->sum('total_price'),
        ];
    }
}