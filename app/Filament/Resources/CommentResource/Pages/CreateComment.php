<?php

namespace App\Filament\Resources\CommentResource\Pages;

use App\Filament\Resources\CommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 自动填充IP和User Agent
        $data['author_ip'] = request()->ip();
        $data['author_user_agent'] = request()->userAgent();
        
        // 如果是登录用户，自动填充用户信息
        if (auth()->check() && empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
            $data['author_name'] = auth()->user()->name;
            $data['author_email'] = auth()->user()->email;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
