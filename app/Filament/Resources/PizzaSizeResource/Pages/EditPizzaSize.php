<?php

namespace App\Filament\Resources\PizzaSizeResource\Pages;

use App\Filament\Resources\PizzaSizeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPizzaSize extends EditRecord
{
    protected static string $resource = PizzaSizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
