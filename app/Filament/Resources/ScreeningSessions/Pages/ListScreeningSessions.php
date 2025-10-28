<?php

namespace App\Filament\Resources\ScreeningSessions\Pages;

use App\Filament\Resources\ScreeningSessions\ScreeningSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScreeningSessions extends ListRecords
{
    protected static string $resource = ScreeningSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
