<?php

namespace App\Filament\Widgets;

use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestPosts extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = '最新文章';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('title')
                    ->label('标题')
                    ->limit(40)
                    ->url(fn ($record) => route('filament.admin.resources.posts.edit', $record)),

                TextColumn::make('category.name')
                    ->label('分类')
                    ->badge(),

                TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                TextColumn::make('view_count')
                    ->label('浏览')
                    ->numeric(),

                TextColumn::make('published_at')
                    ->label('发布时间')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('未发布'),
            ])
            ->paginated(false);
    }
}
