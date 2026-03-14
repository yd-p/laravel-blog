<?php

namespace App\Filament\Resources\ScheduledJobs\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class ScheduledJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('任务名称')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('command')
                    ->label('命令')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('cron')
                    ->label('Cron')
                    ->fontFamily('mono'),

                IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('without_overlapping')
                    ->label('禁重叠')
                    ->boolean(),

                IconColumn::make('run_in_background')
                    ->label('后台')
                    ->boolean(),

                TextColumn::make('last_run_at')
                    ->label('上次运行')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('从未运行')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('启用状态'),
            ])
            ->recordActions([
                Action::make('run')
                    ->label('立即执行')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('立即执行')
                    ->modalDescription(fn ($record) => "确认立即执行「{$record->name}」（{$record->command}）？")
                    ->action(function ($record): void {
                        Artisan::queue($record->command);
                        $record->update(['last_run_at' => now()]);
                    })
                    ->successNotificationTitle('已加入执行队列'),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
