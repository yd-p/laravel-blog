<?php

namespace App\Filament\Resources\ScheduledTasks\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class ScheduledTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('任务名称')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'command' => 'info',
                        'job'     => 'warning',
                        'shell'   => 'gray',
                        default   => 'gray',
                    }),

                TextColumn::make('cron_expression')
                    ->label('Cron 表达式'),

                TextColumn::make('grace_time_in_minutes')
                    ->label('宽限时间(分)')
                    ->suffix(' 分钟'),

                IconColumn::make('last_status')
                    ->label('上次状态')
                    ->state(function ($record): string {
                        if ($record->last_failed_at) {
                            return 'failed';
                        }
                        if ($record->last_finished_at) {
                            return 'success';
                        }
                        if ($record->last_started_at) {
                            return 'running';
                        }
                        return 'pending';
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'success' => 'heroicon-o-check-circle',
                        'failed'  => 'heroicon-o-x-circle',
                        'running' => 'heroicon-o-arrow-path',
                        default   => 'heroicon-o-clock',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'success' => 'success',
                        'failed'  => 'danger',
                        'running' => 'warning',
                        default   => 'gray',
                    }),

                TextColumn::make('last_started_at')
                    ->label('上次开始')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('从未运行')
                    ->sortable(),

                TextColumn::make('last_finished_at')
                    ->label('上次完成')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('last_failed_at')
                    ->label('上次失败')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('—')
                    ->color('danger')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('run')
                    ->label('立即执行')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('立即执行任务')
                    ->modalDescription(fn ($record) => "确认立即执行「{$record->name}」？")
                    ->action(function ($record): void {
                        if ($record->type === 'command') {
                            // 提取命令名（去掉参数）
                            $parts = explode(' ', $record->name, 2);
                            $command = $parts[0];
                            $args = isset($parts[1]) ? ['--' . ltrim($parts[1], '--')] : [];
                            Artisan::queue($command);
                        }
                    })
                    ->successNotificationTitle('任务已加入队列'),
            ])
            ->toolbarActions([
                Action::make('sync')
                    ->label('同步任务列表')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (): void {
                        Artisan::call('schedule-monitor:sync');
                    })
                    ->successNotificationTitle('同步完成'),
            ])
            ->defaultSort('name');
    }
}
