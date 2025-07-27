<?php

namespace App\Filament\Resources\PizzaSizeResource\Pages;

use App\Filament\Resources\PizzaSizeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPizzaSizes extends ListRecords
{
    protected static string $resource = PizzaSizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
