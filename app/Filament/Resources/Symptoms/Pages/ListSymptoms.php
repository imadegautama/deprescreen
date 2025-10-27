<?php

namespace App\Filament\Resources\Symptoms\Pages;

use App\Filament\Resources\Symptoms\SymptomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSymptoms extends ListRecords
{
    protected static string $resource = SymptomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
