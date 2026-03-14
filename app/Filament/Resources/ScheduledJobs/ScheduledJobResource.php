<?php

namespace App\Filament\Resources\ScheduledJobs;

use App\Filament\Resources\ScheduledJobs\Pages\CreateScheduledJob;
use App\Filament\Resources\ScheduledJobs\Pages\EditScheduledJob;
use App\Filament\Resources\ScheduledJobs\Pages\ListScheduledJobs;
use App\Filament\Resources\ScheduledJobs\Schemas\ScheduledJobForm;
use App\Filament\Resources\ScheduledJobs\Tables\ScheduledJobsTable;
use App\Models\ScheduledJob;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScheduledJobResource extends Resource
{
    protected static ?string $model = ScheduledJob::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = '自定义任务';

    protected static ?string $modelLabel = '定时任务';

    protected static ?string $pluralModelLabel = '自定义定时任务';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return '系统设置';
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduledJobForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduledJobsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListScheduledJobs::route('/'),
            'create' => CreateScheduledJob::route('/create'),
            'edit'   => EditScheduledJob::route('/{record}/edit'),
        ];
    }
}
