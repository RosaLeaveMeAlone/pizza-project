<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\PizzaSize;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    
    protected static ?string $title = 'Productos del Pedido';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Este form no se usa ya que solo mostramos los productos
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Cantidad'),
                Tables\Columns\TextColumn::make('pivot.pizza_size_id')
                    ->label('Tamaño')
                    ->formatStateUsing(function ($state) {
                        return $state ? PizzaSize::find($state)?->name : 'N/A';
                    }),
                Tables\Columns\TextColumn::make('pivot.unit_price')
                    ->label('Precio Unitario')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('pivot.unit_price')
                    ->label('Subtotal')
                    ->formatStateUsing(function ($state, $record) {
                        $subtotal = $record->pivot->unit_price * $record->pivot->quantity;
                        return '$' . number_format($subtotal, 2);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Removemos las acciones de crear ya que es solo para visualización
            ])
            ->actions([
                // Removemos las acciones de editar/eliminar ya que es solo para visualización
            ])
            ->bulkActions([
                // Removemos las acciones masivas
            ]);
    }
}
