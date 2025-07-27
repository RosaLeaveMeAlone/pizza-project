<?php

namespace App\Filament\Resources\PizzaResource\Pages;

use App\Filament\Resources\PizzaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPizzas extends ListRecords
{
    protected static string $resource = PizzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
