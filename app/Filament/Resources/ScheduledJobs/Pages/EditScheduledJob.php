<?php

namespace App\Filament\Resources\ScheduledJobs\Pages;

use App\Filament\Resources\ScheduledJobs\ScheduledJobResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;

class EditScheduledJob extends EditRecord
{
    protected static string $resource = ScheduledJobResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        // 保存后立即同步到 schedule-monitor
        Artisan::call('schedule-monitor:sync');
    }
}
