<?php

namespace App\Filament\Resources\Symptoms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SymptomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                Toggle::make('is_core')
                    ->required(),
                Toggle::make('is_sensitive')
                    ->required(),
                Select::make('type')
                    ->options(['scale' => 'Scale', 'boolean' => 'Boolean'])
                    ->default('scale')
                    ->required(),
            ]);
    }
}
