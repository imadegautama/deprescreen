<?php

namespace App\Filament\Resources\Thresholds;

use App\Filament\Resources\Thresholds\Pages\CreateThreshold;
use App\Filament\Resources\Thresholds\Pages\EditThreshold;
use App\Filament\Resources\Thresholds\Pages\ListThresholds;
use App\Filament\Resources\Thresholds\Schemas\ThresholdForm;
use App\Filament\Resources\Thresholds\Tables\ThresholdsTable;
use App\Models\Threshold;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ThresholdResource extends Resource
{
    protected static ?string $model = Threshold::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Threshold';

    public static function form(Schema $schema): Schema
    {
        return ThresholdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThresholdsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThresholds::route('/'),
            'create' => CreateThreshold::route('/create'),
            'edit' => EditThreshold::route('/{record}/edit'),
        ];
    }
}
