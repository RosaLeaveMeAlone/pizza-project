<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PizzaSizeResource\Pages;
use App\Filament\Resources\PizzaSizeResource\RelationManagers;
use App\Models\PizzaSize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PizzaSizeResource extends Resource
{
    protected static ?string $model = PizzaSize::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Personal, Mediana, Familiar'),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(2)
                    ->placeholder('Ej: 30cm de diámetro'),
                Forms\Components\TextInput::make('price_multiplier')
                    ->label('Multiplicador de Precio')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->placeholder('Ej: 1.00, 1.50, 2.00')
                    ->helperText('Factor por el que se multiplica el precio base'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),
                Tables\Columns\TextColumn::make('price_multiplier')
                    ->label('Multiplicador')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
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
            'index' => Pages\ListPizzaSizes::route('/'),
            'create' => Pages\CreatePizzaSize::route('/create'),
            'edit' => Pages\EditPizzaSize::route('/{record}/edit'),
        ];
    }
}
