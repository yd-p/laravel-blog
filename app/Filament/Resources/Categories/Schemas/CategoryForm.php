<?php

namespace App\Filament\Resources\Categories\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()->schema([
                    Tabs\Tab::make('内容设置')->schema([
                        SelectTree::make('parent_id')
                            ->label('父分类')
                            ->relationship('parent', 'name', 'parent_id')
                            ->placeholder('请选择父分类')
                            ->withCount()
                            ->searchable()
                            ->defaultOpenLevel(99),
                        TextInput::make('name')
                            ->label('分类名称')
                            ->required()
                            ->placeholder('请输入分类名称'),
                        TextInput::make('slug')
                            ->label('分类别名')
                            ->placeholder('请输入分类别名（英文/数字/横杠）'),
                        Textarea::make('description')
                            ->label('分类描述')
                            ->columnSpanFull()
                            ->placeholder('请输入分类描述（选填）'),

                        Toggle::make('status')
                            ->label('状态')
                            ->required()
                            ->default(1)
                            ->helperText('1=启用，0=禁用'),
                    ])
                ]),
                Tabs::make()->schema([
                    Tabs\Tab::make('SEO设置')->schema([
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
                    ])
                ]),
            ]);
    }
}
