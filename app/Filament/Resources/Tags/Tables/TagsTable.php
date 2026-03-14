<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('标签名称')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('标签别名')
                    ->searchable(),

                TextColumn::make('color')
                    ->label('标签颜色')
                    ->searchable()
                    ->placeholder('未设置')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) return '未设置';
                        return "<span style='display:inline-block;width:16px;height:16px;background:{$state};border:1px solid #eee;margin-right:4px;'></span>{$state}";
                    })
                    ->html(),

                TextColumn::make('post_count')
                    ->label('关联文章数')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('标签状态')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == 1 ? '启用' : '禁用')
                    ->color(fn ($state) => $state == 1 ? 'success' : 'danger'),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make()
                    ->label('编辑'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('删除'),
                ])->label('批量操作'),
            ]);
    }
}
