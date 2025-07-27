<?php

namespace App\Filament\Resources\PizzaResource\Pages;

use App\Filament\Resources\PizzaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePizza extends CreateRecord
{
    protected static string $resource = PizzaResource::class;
}
