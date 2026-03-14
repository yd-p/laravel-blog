<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComment extends ViewRecord
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
