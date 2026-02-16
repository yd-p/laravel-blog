<?php

namespace App\Filament\Resources\Media\Tables;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('预览')
                    ->disk('public')
                    ->size(60)
                    ->defaultImageUrl(fn (Media $record) => MediaResource::getDefaultImage($record)),

                TextColumn::make('name')
                    ->label('名称')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (Media $record): string => $record->name),

                TextColumn::make('file_name')
                    ->label('文件名')
                    ->searchable()
                    ->limit(20)
                    ->toggleable()
                    ->tooltip(fn (Media $record): string => $record->file_name),

                TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'image' => 'success',
                        'video' => 'warning',
                        'audio' => 'info',
                        'document' => 'primary',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'image' => 'heroicon-o-photo',
                        'video' => 'heroicon-o-film',
                        'audio' => 'heroicon-o-musical-note',
                        'document' => 'heroicon-o-document-text',
                        default => 'heroicon-o-document',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'image' => '图片',
                        'video' => '视频',
                        'audio' => '音频',
                        'document' => '文档',
                        default => '其他',
                    }),

                TextColumn::make('collection_name')
                    ->label('集合')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('size')
                    ->label('大小')
                    ->formatStateUsing(fn ($state) => MediaResource::formatBytes($state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('width')
                    ->label('尺寸')
                    ->formatStateUsing(fn (Media $record) => 
                        $record->width && $record->height 
                            ? "{$record->width}×{$record->height}" 
                            : '-'
                    )
                    ->toggleable(),

                TextColumn::make('uploadedBy.name')
                    ->label('上传者')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('上传时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('文件类型')
                    ->options([
                        'image' => '图片',
                        'video' => '视频',
                        'audio' => '音频',
                        'document' => '文档',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->ofType($data['value']);
                        }
                        return $query;
                    }),

                SelectFilter::make('collection_name')
                    ->label('集合')
                    ->options([
                        'default' => '默认',
                        'posts' => '文章',
                        'products' => '产品',
                        'avatars' => '头像',
                        'banners' => '横幅',
                        'documents' => '文档',
                    ]),

                SelectFilter::make('uploaded_by')
                    ->label('上传者')
                    ->relationship('uploadedBy', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('上传日期从'),
                        DatePicker::make('created_until')
                            ->label('上传日期到'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                TrashedFilter::make()
                    ->label('显示已删除数据'),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('下载')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Media $record) => Storage::disk($record->disk)->download($record->path, $record->file_name))
                    ->color('success'),

                Action::make('copy_url')
                    ->label('复制链接')
                    ->icon('heroicon-o-clipboard')
                    ->url(fn (Media $record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('info'),

                ViewAction::make()
                    ->label('查看'),

                EditAction::make()
                    ->label('编辑'),

                DeleteAction::make()
                    ->label('删除'),

                ForceDeleteAction::make()
                    ->label('强制删除'),

                RestoreAction::make()
                    ->label('恢复'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('删除'),

                    ForceDeleteBulkAction::make()
                        ->label('强制删除'),

                    RestoreBulkAction::make()
                        ->label('恢复'),
                    
                    BulkAction::make('change_collection')
                        ->label('更改集合')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Select::make('collection_name')
                                ->label('目标集合')
                                ->options([
                                    'default' => '默认',
                                    'posts' => '文章',
                                    'products' => '产品',
                                    'avatars' => '头像',
                                    'banners' => '横幅',
                                    'documents' => '文档',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each->update(['collection_name' => $data['collection_name']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ])->label('批量操作'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
