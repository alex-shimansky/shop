<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Tables\Columns\TextColumn;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Translations')
                ->tabs([
                    Tabs\Tab::make('🇺🇸 English')->schema([
                        TextInput::make('name.en')->label('Name (EN)')->required(),
                        Textarea::make('description.en')->label('Description (EN)'),
                    ]),
                    Tabs\Tab::make('🇺🇦 Українська')->schema([
                        TextInput::make('name.uk')->label('Name (UA)')->required(),
                        Textarea::make('description.uk')->label('Description (UA)'),
                    ]),
                    Tabs\Tab::make('🇪🇸 Español')->schema([
                        TextInput::make('name.es')->label('Name (ES)'),
                        Textarea::make('description.es')->label('Description (ES)'),
                    ]),
                ])
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label('Name')
                ->getStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                ->searchable(query: function ($query, string $search): void {
                    $locale = app()->getLocale();
                    $query->where("name->{$locale}", 'like', "%{$search}%");
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
