<?php

namespace App\Filament\Resources\ScreeningSessions;

use App\Filament\Resources\ScreeningSessions\Pages\CreateScreeningSession;
use App\Filament\Resources\ScreeningSessions\Pages\EditScreeningSession;
use App\Filament\Resources\ScreeningSessions\Pages\ListScreeningSessions;
use App\Filament\Resources\ScreeningSessions\Pages\ViewScreeningSession;
use App\Filament\Resources\ScreeningSessions\Schemas\ScreeningSessionForm;
use App\Filament\Resources\ScreeningSessions\Schemas\ScreeningSessionInfolist;
use App\Filament\Resources\ScreeningSessions\Tables\ScreeningSessionsTable;
use App\Models\ScreeningSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScreeningSessionResource extends Resource
{
    protected static ?string $model = ScreeningSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ScreeningSession';

    public static function form(Schema $schema): Schema
    {
        return ScreeningSessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScreeningSessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScreeningSessionsTable::configure($table);
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
            'index' => ListScreeningSessions::route('/'),
            'create' => CreateScreeningSession::route('/create'),
            'view' => ViewScreeningSession::route('/{record}'),
            'edit' => EditScreeningSession::route('/{record}/edit'),
        ];
    }
}
