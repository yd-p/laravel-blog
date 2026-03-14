<?php

namespace App\Filament\Resources\ScheduledJobs\Pages;

use App\Filament\Resources\ScheduledJobs\ScheduledJobResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Artisan;

class CreateScheduledJob extends CreateRecord
{
    protected static string $resource = ScheduledJobResource::class;

    protected function afterCreate(): void
    {
        // 创建后立即同步到 schedule-monitor
        Artisan::call('schedule-monitor:sync');
    }
}
