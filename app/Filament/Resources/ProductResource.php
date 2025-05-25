<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Translations')
                    ->tabs([
                        Tabs\Tab::make('🇺🇸 English')->schema([
                            TextInput::make('name.en')
                                ->label('Name (EN)')
                                ->required(),
                            Textarea::make('description.en')
                                ->label('Description (EN)'),
                        ]),
                        Tabs\Tab::make('🇺🇦 Українська')->schema([
                            TextInput::make('name.uk')
                                ->label('Name (UA)')
                                ->required(),
                            Textarea::make('description.uk')
                                ->label('Description (UA)'),
                        ]),
                        Tabs\Tab::make('🇪🇸 Español')->schema([
                            TextInput::make('name.es')
                                ->label('Name (ES)'),
                            Textarea::make('description.es')
                                ->label('Description (ES)'),
                        ]),
                    ])
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->numeric()
                    ->label('Price')
                    ->required()
                    ->minValue(0),

                TextInput::make('quantity')
                    ->numeric()
                    ->label('Quantity')
                    ->required()
                    ->minValue(0),

                FileUpload::make('image')
                    ->label('Изображение')
                    ->image()
                    ->imagePreviewHeight('250')
                    ->directory('products')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['image/webp', 'image/png', 'image/jpeg'])
                    ->required(),

                Select::make('category_id')
                ->label('Category')
                ->searchable()
                ->getSearchResultsUsing(function (string $search) {
                    $locale = app()->getLocale();
                    return \App\Models\Category::query()
                        ->where("name->{$locale}", 'like', "%{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn ($category) => [
                            $category->id => $category->getTranslation('name', $locale),
                        ]);
                })
                ->getOptionLabelUsing(function ($value) {
                    $locale = app()->getLocale();
                    return \App\Models\Category::find($value)?->getTranslation('name', $locale);
                })
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable(),

                TextColumn::make('price')->label('Price'),
                TextColumn::make('quantity')->label('Quantity'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->getStateUsing(fn ($record) => $record->category?->getTranslation('name', app()->getLocale())),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}