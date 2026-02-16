<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Filament\Resources\Media\MediaResource;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('文件信息')
                    ->schema([
                        TextInput::make('name')
                            ->label('文件名称')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('path')
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

                        Select::make('collection_name')
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
                            ->searchable()
                            ->native(false),

                        Select::make('uploaded_by')
                            ->label('上传者')
                            ->relationship('uploadedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('自定义属性')
                    ->schema([
                        KeyValue::make('custom_properties')
                            ->label('自定义属性')
                            ->keyLabel('属性名')
                            ->valueLabel('属性值')
                            ->addActionLabel('添加属性')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('元数据')
                    ->schema([
                        TextInput::make('file_name')
                            ->label('原始文件名')
                            ->disabled(),

                        TextInput::make('mime_type')
                            ->label('MIME类型')
                            ->disabled(),

                        TextInput::make('size')
                            ->label('文件大小')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => MediaResource::formatBytes($state)),

                        TextInput::make('width')
                            ->label('宽度')
                            ->disabled()
                            ->suffix('px'),

                        TextInput::make('height')
                            ->label('高度')
                            ->disabled()
                            ->suffix('px'),

                        TextInput::make('disk')
                            ->label('存储磁盘')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
