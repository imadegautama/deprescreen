<?php

namespace App\Filament\Resources\ScreeningSessions\Pages;

use App\Filament\Resources\ScreeningSessions\ScreeningSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditScreeningSession extends EditRecord
{
    protected static string $resource = ScreeningSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
