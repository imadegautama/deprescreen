<?php

namespace App\Filament\Resources\ScreeningSessions\Pages;

use App\Filament\Resources\ScreeningSessions\ScreeningSessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewScreeningSession extends ViewRecord
{
    protected static string $resource = ScreeningSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
