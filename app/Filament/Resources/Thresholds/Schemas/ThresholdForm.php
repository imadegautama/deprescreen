<?php

namespace App\Filament\Resources\Thresholds\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ThresholdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('level')
                    ->required(),
                TextInput::make('min_score')
                    ->required()
                    ->numeric(),
                TextInput::make('max_score')
                    ->required()
                    ->numeric(),
                Textarea::make('advice_text')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
