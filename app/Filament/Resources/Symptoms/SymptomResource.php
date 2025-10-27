<?php

namespace App\Filament\Resources\Symptoms;

use App\Filament\Resources\Symptoms\Pages\CreateSymptom;
use App\Filament\Resources\Symptoms\Pages\EditSymptom;
use App\Filament\Resources\Symptoms\Pages\ListSymptoms;
use App\Filament\Resources\Symptoms\Schemas\SymptomForm;
use App\Filament\Resources\Symptoms\Tables\SymptomsTable;
use App\Models\Symptom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SymptomResource extends Resource
{
    protected static ?string $model = Symptom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Symptom';

    public static function form(Schema $schema): Schema
    {
        return SymptomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SymptomsTable::configure($table);
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
            'index' => ListSymptoms::route('/'),
            'create' => CreateSymptom::route('/create'),
            'edit' => EditSymptom::route('/{record}/edit'),
        ];
    }
}
