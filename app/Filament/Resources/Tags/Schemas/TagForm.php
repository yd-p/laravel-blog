<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('标签名称')
                    ->required()
                    ->placeholder('请输入标签名称'),

                TextInput::make('slug')
                    ->label('标签别名')
                    ->required()
                    ->placeholder('请输入标签别名（英文/数字/横杠，用于URL）'),

                Textarea::make('description')
                    ->label('标签描述')
                    ->columnSpanFull()
                    ->placeholder('请输入标签描述（选填）'),

                TextInput::make('color')
                    ->label('标签颜色')
                    ->placeholder('请输入颜色值（如 #ff0000 或 red，选填）')
                    ->helperText('支持十六进制色值或英文颜色名，用于前端展示标签样式'),

                TextInput::make('post_count')
                    ->label('关联文章数')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->placeholder('初始关联文章数量，默认0')
                    ->readOnly(),

                TextInput::make('status')
                    ->label('标签状态')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->helperText('1=启用，0=禁用')
                    ->placeholder('请输入1（启用）或0（禁用）'),
            ]);
    }
}
