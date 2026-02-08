<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent.name')
                    ->label('父分类')
                    ->searchable()
                    ->placeholder('无'),

                TextColumn::make('name')
                    ->label('分类名称')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('分类别名')
                    ->searchable(),

                TextColumn::make('seo_title')
                    ->label('SEO标题')
                    ->searchable()
                    ->placeholder('未设置'),

                TextColumn::make('seo_keywords')
                    ->label('SEO关键词')
                    ->searchable()
                    ->placeholder('未设置'),

                TextColumn::make('sort')
                    ->label('排序值')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('状态')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return $state == 1 ? '启用' : '禁用';
                    })
                    ->color(function ($state) {
                        return $state == 1 ? 'success' : 'danger';
                    }),

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
                    ->label('显示已删除数据'),
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
