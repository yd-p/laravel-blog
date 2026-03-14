<?php

namespace App\Filament\Resources\ScheduledJobs\Pages;

use App\Filament\Resources\ScheduledJobs\ScheduledJobResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScheduledJobs extends ListRecords
{
    protected static string $resource = ScheduledJobResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
