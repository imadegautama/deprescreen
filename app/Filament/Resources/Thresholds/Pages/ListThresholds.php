<?php

namespace App\Filament\Resources\Thresholds\Pages;

use App\Filament\Resources\Thresholds\ThresholdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListThresholds extends ListRecords
{
    protected static string $resource = ThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
