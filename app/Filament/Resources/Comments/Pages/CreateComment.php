<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 自动填充 IP 地址和 User Agent
        $data['author_ip'] = request()->ip();
        $data['author_user_agent'] = request()->userAgent();

        return $data;
    }
}
