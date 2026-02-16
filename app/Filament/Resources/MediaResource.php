<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = '媒体库';

    protected static ?string $modelLabel = '媒体文件';

    protected static ?string $pluralModelLabel = '媒体库';

    protected static ?string $navigationGroup = '内容管理';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('文件信息')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('文件名称')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('path')
                            ->label('文件')
                            ->disk('public')
                            ->directory('media')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->acceptedFileTypes(['image/*', 'video/*', 'audio/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('collection_name')
                            ->label('集合')
                            ->options([
                                'default' => '默认',
                                'posts' => '文章',
                                'products' => '产品',
                                'avatars' => '头像',
                                'banners' => '横幅',
                                'documents' => '文档',
                            ])
                            ->default('default')
                            ->searchable(),

                        Forms\Components\Select::make('uploaded_by')
                            ->label('上传者')
                            ->relationship('uploadedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('自定义属性')
                    ->schema([
                        Forms\Components\KeyValue::make('custom_properties')
                            ->label('自定义属性')
                            ->keyLabel('属性名')
                            ->valueLabel('属性值')
                            ->addActionLabel('添加属性')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('元数据')
                    ->schema([
                        Forms\Components\TextInput::make('file_name')
                            ->label('原始文件名')
                            ->disabled(),

                        Forms\Components\TextInput::make('mime_type')
                            ->label('MIME类型')
                            ->disabled(),

                        Forms\Components\TextInput::make('size')
                            ->label('文件大小')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => self::formatBytes($state)),

                        Forms\Components\TextInput::make('width')
                            ->label('宽度')
                            ->disabled()
                            ->suffix('px'),

                        Forms\Components\TextInput::make('height')
                            ->label('高度')
                            ->disabled()
                            ->suffix('px'),

                        Forms\Components\TextInput::make('disk')
                            ->label('存储磁盘')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('预览')
                    ->disk('public')
                    ->size(60)
                    ->defaultImageUrl(fn (Media $record) => self::getDefaultImage($record)),

                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (Media $record): string => $record->name),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('文件名')
                    ->searchable()
                    ->limit(20)
                    ->toggleable()
                    ->tooltip(fn (Media $record): string => $record->file_name),

                Tables\Columns\TextColumn::make('type')
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

                Tables\Columns\TextColumn::make('collection_name')
                    ->label('集合')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('size')
                    ->label('大小')
                    ->formatStateUsing(fn ($state) => self::formatBytes($state))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('width')
                    ->label('尺寸')
                    ->formatStateUsing(fn (Media $record) => 
                        $record->width && $record->height 
                            ? "{$record->width}×{$record->height}" 
                            : '-'
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('上传者')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('上传时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
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

                Tables\Filters\SelectFilter::make('collection_name')
                    ->label('集合')
                    ->options([
                        'default' => '默认',
                        'posts' => '文章',
                        'products' => '产品',
                        'avatars' => '头像',
                        'banners' => '横幅',
                        'documents' => '文档',
                    ]),

                Tables\Filters\SelectFilter::make('uploaded_by')
                    ->label('上传者')
                    ->relationship('uploadedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('上传日期从'),
                        Forms\Components\DatePicker::make('created_until')
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

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('下载')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Media $record) => Storage::disk($record->disk)->download($record->path, $record->file_name))
                    ->color('success'),

                Tables\Actions\Action::make('copy_url')
                    ->label('复制链接')
                    ->icon('heroicon-o-clipboard')
                    ->url(fn (Media $record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('info'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('change_collection')
                        ->label('更改集合')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Forms\Components\Select::make('collection_name')
                                ->label('目标集合')
                                ->options([
                                    'default' => '默认',
                                    'posts' => '文章',
                                    'products' => '产品',
                                    'avatars' => '头像',
                                    'banners' => '横幅',
                                    'documents' => '文档',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each->update(['collection_name' => $data['collection_name']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                //
            ]);
    }

    /**
     * 格式化字节大小
     */
    protected static function formatBytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 获取默认图片
     */
    protected static function getDefaultImage(Media $record): string
    {
        return match ($record->type) {
            'video' => 'https://via.placeholder.com/60/3b82f6/ffffff?text=Video',
            'audio' => 'https://via.placeholder.com/60/8b5cf6/ffffff?text=Audio',
            'document' => 'https://via.placeholder.com/60/10b981/ffffff?text=Doc',
            default => 'https://via.placeholder.com/60/6b7280/ffffff?text=File',
        };
    }
}
