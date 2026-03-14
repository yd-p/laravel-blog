<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        // 基本信息 - 第一行
                        SelectTree::make('category_id')
                            ->label('文章分类')
                            ->relationship('category', 'name', 'parent_id')
                            ->placeholder('请选择文章所属分类')
                            ->withCount()
                            ->searchable()
                            ->defaultOpenLevel(99)
                            ->columnSpan(1),

                        Select::make('author_id')
                            ->label('文章作者')
                            ->relationship('author', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->native(false)
                            ->placeholder('请选择文章作者')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('文章状态')
                            ->required()
                            ->options(PostStatus::toSelectArray())
                            ->default(PostStatus::DRAFT->value)
                            ->native(false)
                            ->columnSpan(1),

                        DateTimePicker::make('published_at')
                            ->label('发布时间')
                            ->placeholder('请选择文章发布时间（选填）')
                            ->native(false)
                            ->columnSpan(1),

                        // 第二行
                        TextInput::make('title')
                            ->label('文章标题')
                            ->required()
                            ->placeholder('请输入文章标题')
                            ->columnSpan(3),

                        TextInput::make('slug')
                            ->label('文章别名')
                            ->required()
                            ->placeholder('请输入文章别名（英文/数字/横杠，用于URL）')
                            ->columnSpan(1),

                        // 第三行
                        TextInput::make('thumbnail')
                            ->label('缩略图URL')
                            ->placeholder('请输入文章缩略图的完整URL（选填）')
                            ->columnSpan(3),

                        TextInput::make('view_count')
                            ->label('浏览次数')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->placeholder('初始浏览次数，默认0')
                            ->columnSpan(1),

                        // 内容区域
                        Textarea::make('excerpt')
                            ->label('文章摘要')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('请输入文章简短摘要（选填）'),

                        RichEditor::make('content')
                            ->label('文章内容')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('请输入文章正文内容'),

                        // SEO 设置
                        TextInput::make('seo_title')
                            ->label('SEO标题')
                            ->placeholder('请输入SEO标题（选填）')
                            ->columnSpan(2),

                        TextInput::make('seo_keywords')
                            ->label('SEO关键词')
                            ->placeholder('请输入SEO关键词，多个用逗号分隔（选填）')
                            ->columnSpan(2),

                        Textarea::make('seo_description')
                            ->label('SEO描述')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('请输入SEO描述（选填）'),
                    ])
                    ->columns(4),
            ]);
    }
}
