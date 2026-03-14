<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestComments extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = '最新评论';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Comment::query()->with('post')->latest()->limit(5))
            ->columns([
                TextColumn::make('author_name')
                    ->label('评论者'),

                TextColumn::make('post.title')
                    ->label('文章')
                    ->limit(30),

                TextColumn::make('content')
                    ->label('内容')
                    ->limit(50),

                TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
