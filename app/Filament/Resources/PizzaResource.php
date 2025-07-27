<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PizzaResource\Pages;
use App\Filament\Resources\PizzaResource\RelationManagers;
use App\Models\Pizza;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PizzaResource extends Resource
{
    protected static ?string $model = Pizza::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    
    protected static ?string $navigationGroup = 'Gestión de Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name', fn (Builder $query) => $query
                        ->whereHas('category', fn (Builder $query) => $query
                            ->where('name', 'like', '%Pizza%')
                        )
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('base_price')
                            ->label('Precio Base')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ]),
                Forms\Components\Select::make('ingredients')
                    ->label('Ingredientes')
                    ->relationship('ingredients', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Pizza')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.base_price')
                    ->label('Precio Base')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredients_count')
                    ->label('Ingredientes')
                    ->counts('ingredients'),
                Tables\Columns\IconColumn::make('product.available')
                    ->label('Disponible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPizzas::route('/'),
            'create' => Pages\CreatePizza::route('/create'),
            'edit' => Pages\EditPizza::route('/{record}/edit'),
        ];
    }
}
