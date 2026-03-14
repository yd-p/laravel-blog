<?php

namespace App\Filament\Resources\ScheduledTasks;

use App\Filament\Resources\ScheduledTasks\Pages\ListScheduledTasks;
use App\Filament\Resources\ScheduledTasks\Tables\ScheduledTasksTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

class ScheduledTaskResource extends Resource
{
    protected static ?string $model = MonitoredScheduledTask::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = '定时任务';

    protected static ?string $modelLabel = '定时任务';

    protected static ?string $pluralModelLabel = '定时任务';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return '系统设置';
    }

    public static function table(Table $table): Table
    {
        return ScheduledTasksTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduledTasks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
