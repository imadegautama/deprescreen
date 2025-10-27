<?php

namespace App\Filament\Resources\Thresholds\Pages;

use App\Filament\Resources\Thresholds\ThresholdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditThreshold extends EditRecord
{
    protected static string $resource = ThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
