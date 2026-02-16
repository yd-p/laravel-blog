<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Enums\CommentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('评论信息')
                    ->schema([
                        Select::make('post_id')
                            ->label('所属文章')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('parent_id')
                            ->label('父评论')
                            ->relationship('parent', 'id')
                            ->searchable()
                            ->preload()
                            ->placeholder('无（顶级评论）')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                substr($record->content, 0, 50) . '...'
                            ),

                        Select::make('status')
                            ->label('状态')
                            ->options(CommentStatus::toSelectArray())
                            ->required()
                            ->default(CommentStatus::PENDING->value)
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('评论者信息')
                    ->schema([
                        Select::make('user_id')
                            ->label('关联用户')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('游客评论'),

                        TextInput::make('author_name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('author_email')
                            ->label('邮箱')
                            ->email()
                            ->required()
                            ->maxLength(100),

                        TextInput::make('author_url')
                            ->label('网址')
                            ->url()
                            ->maxLength(200),

                        TextInput::make('author_ip')
                            ->label('IP地址')
                            ->disabled()
                            ->maxLength(45),

                        Textarea::make('author_user_agent')
                            ->label('用户代理')
                            ->disabled()
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('评论内容')
                    ->schema([
                        Textarea::make('content')
                            ->label('内容')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make('审核信息')
                    ->schema([
                        Select::make('approved_by')
                            ->label('审核人')
                            ->relationship('approvedBy', 'name')
                            ->disabled(),

                        DateTimePicker::make('approved_at')
                            ->label('审核时间')
                            ->disabled(),

                        TextInput::make('karma')
                            ->label('评分')
                            ->numeric()
                            ->default(0),

                        TextInput::make('reply_count')
                            ->label('回复数')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
