<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('文章分类')
                    ->searchable()
                    ->placeholder('未分类'),

                TextColumn::make('title')
                    ->label('文章标题')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('文章别名')
                    ->searchable(),

                TextColumn::make('thumbnail')
                    ->label('缩略图')
                    ->searchable()
                    ->placeholder('未设置')
                    ->limit(30),

                TextColumn::make('status')
                    ->label('文章状态')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return $state == 1 ? '发布' : '草稿';
                    })
                    ->color(function ($state) {
                        return $state == 1 ? 'success' : 'warning';
                    }),

                TextColumn::make('published_at')
                    ->label('发布时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->placeholder('未发布'),

                TextColumn::make('view_count')
                    ->label('浏览次数')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('seo_title')
                    ->label('SEO标题')
                    ->searchable()
                    ->placeholder('未设置'),

                TextColumn::make('seo_keywords')
                    ->label('SEO关键词')
                    ->searchable()
                    ->placeholder('未设置'),

                TextColumn::make('author.name')
                    ->label('文章作者')
                    ->searchable()
                    ->placeholder('未知作者'),

                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('删除时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('显示已删除文章'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('编辑'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('删除'),
                    ForceDeleteBulkAction::make()
                        ->label('强制删除'),
                    RestoreBulkAction::make()
                        ->label('恢复'),
                ])->label('批量操作'),
            ]);
    }
}
