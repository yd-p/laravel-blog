<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('文章分类')
                    ->relationship('category', 'name')
                    ->required()
                    ->placeholder('请选择文章所属分类'),

                TextInput::make('title')
                    ->label('文章标题')
                    ->required()
                    ->placeholder('请输入文章标题'),

                TextInput::make('slug')
                    ->label('文章别名')
                    ->required()
                    ->placeholder('请输入文章别名（英文/数字/横杠，用于URL）'),

                Textarea::make('excerpt')
                    ->label('文章摘要')
                    ->columnSpanFull()
                    ->placeholder('请输入文章简短摘要（选填）'),

                Textarea::make('content')
                    ->label('文章内容')
                    ->required()
                    ->columnSpanFull()
                    ->placeholder('请输入文章正文内容'),

                TextInput::make('thumbnail')
                    ->label('缩略图URL')
                    ->placeholder('请输入文章缩略图的完整URL（选填）'),

                TextInput::make('status')
                    ->label('文章状态')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->helperText('1=发布，0=草稿')
                    ->placeholder('请输入1（发布）或0（草稿）'),

                DateTimePicker::make('published_at')
                    ->label('发布时间')
                    ->placeholder('请选择文章发布时间（选填）')
                    ->native(false),

                TextInput::make('view_count')
                    ->label('浏览次数')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->placeholder('初始浏览次数，默认0'),

                TextInput::make('seo_title')
                    ->label('SEO标题')
                    ->placeholder('请输入SEO标题（选填）'),

                TextInput::make('seo_keywords')
                    ->label('SEO关键词')
                    ->placeholder('请输入SEO关键词，多个用逗号分隔（选填）'),

                Textarea::make('seo_description')
                    ->label('SEO描述')
                    ->columnSpanFull()
                    ->placeholder('请输入SEO描述（选填）'),

                Select::make('author_id')
                    ->label('文章作者')
                    ->relationship('author', 'name')
                    ->required()
                    ->placeholder('请选择文章作者'),
            ]);
    }
}
