<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('父分类')
                    ->relationship('parent', 'name')
                    ->placeholder('请选择父分类'),

                TextInput::make('name')
                    ->label('分类名称')
                    ->required()
                    ->placeholder('请输入分类名称'),

                TextInput::make('slug')
                    ->label('分类别名')
                    ->required()
                    ->placeholder('请输入分类别名（英文/数字/横杠）'),

                Textarea::make('description')
                    ->label('分类描述')
                    ->columnSpanFull()
                    ->placeholder('请输入分类描述（选填）'),

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

                TextInput::make('sort')
                    ->label('排序值')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->placeholder('数字越小越靠前，默认0'),

                TextInput::make('status')
                    ->label('状态')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->helperText('1=启用，0=禁用')
                    ->placeholder('请输入1（启用）或0（禁用）'),
            ]);
    }
}
