<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('excerpt')
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('thumbnail'),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
                DateTimePicker::make('published_at'),
                TextInput::make('view_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('seo_title'),
                TextInput::make('seo_keywords'),
                Textarea::make('seo_description')
                    ->columnSpanFull(),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->required(),
            ]);
    }
}
