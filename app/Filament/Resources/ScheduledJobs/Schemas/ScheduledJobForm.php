<?php

namespace App\Filament\Resources\ScheduledJobs\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduledJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('基本信息')->schema([
                TextInput::make('name')
                    ->label('任务名称')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('如：清理日志'),

                TextInput::make('command')
                    ->label('Artisan 命令')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('如：cache:clear 或 inspire')
                    ->helperText('不需要加 php artisan 前缀'),

                TextInput::make('cron')
                    ->label('Cron 表达式')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('* * * * *')
                    ->helperText('分 时 日 月 周 — 例：0 2 * * * 表示每天凌晨2点'),

                Textarea::make('description')
                    ->label('备注')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),

            Section::make('执行选项')->schema([
                Toggle::make('is_active')
                    ->label('启用任务')
                    ->default(true),

                Toggle::make('without_overlapping')
                    ->label('禁止重叠执行')
                    ->default(true)
                    ->helperText('上次未完成时跳过本次'),

                Toggle::make('run_in_background')
                    ->label('后台运行')
                    ->default(false)
                    ->helperText('不阻塞其他任务'),

                Placeholder::make('last_run_at')
                    ->label('上次运行时间')
                    ->content(fn ($record) => $record?->last_run_at?->format('Y-m-d H:i:s') ?? '从未运行'),
            ]),
        ]);
    }
}
