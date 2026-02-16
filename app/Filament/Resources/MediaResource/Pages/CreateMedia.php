<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 自动填充文件信息
        if (isset($data['path'])) {
            $filePath = $data['path'];
            $disk = 'public';
            
            // 获取文件信息
            $data['file_name'] = basename($filePath);
            $data['mime_type'] = Storage::disk($disk)->mimeType($filePath);
            $data['size'] = Storage::disk($disk)->size($filePath);
            $data['disk'] = $disk;
            
            // 如果是图片，获取尺寸
            if (str_starts_with($data['mime_type'], 'image/')) {
                $fullPath = Storage::disk($disk)->path($filePath);
                if (file_exists($fullPath)) {
                    $imageSize = getimagesize($fullPath);
                    if ($imageSize) {
                        $data['width'] = $imageSize[0];
                        $data['height'] = $imageSize[1];
                    }
                }
            }
            
            // 设置上传者
            $data['uploaded_by'] = auth()->id();
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
