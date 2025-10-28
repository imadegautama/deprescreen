<?php

namespace App\Filament\Resources\ScreeningSessions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScreeningSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('score')
                    ->required()
                    ->numeric(),
                TextInput::make('level')
                    ->required(),
                Toggle::make('has_core')
                    ->required(),
                Toggle::make('crisis_flag')
                    ->required(),
            ]);
    }
}
