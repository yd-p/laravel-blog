<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasMedia
{
    /**
     * 获取所有媒体文件
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->orderBy('order_column');
    }

    /**
     * 获取特定集合的媒体文件
     */
    public function getMedia(string $collectionName = 'default'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->media()->where('collection_name', $collectionName)->get();
    }

    /**
     * 获取第一个媒体文件
     */
    public function getFirstMedia(string $collectionName = 'default'): ?Media
    {
        return $this->media()->where('collection_name', $collectionName)->first();
    }

    /**
     * 获取第一个媒体文件的URL
     */
    public function getFirstMediaUrl(string $collectionName = 'default', string $default = ''): string
    {
        $media = $this->getFirstMedia($collectionName);
        return $media ? $media->url : $default;
    }

    /**
     * 添加媒体文件
     */
    public function addMedia(UploadedFile|string $file, string $collectionName = 'default', array $customProperties = []): Media
    {
        if ($file instanceof UploadedFile) {
            return $this->addMediaFromUploadedFile($file, $collectionName, $customProperties);
        }

        return $this->addMediaFromPath($file, $collectionName, $customProperties);
    }

    /**
     * 从上传文件添加媒体
     */
    protected function addMediaFromUploadedFile(UploadedFile $file, string $collectionName, array $customProperties): Media
    {
        $disk = 'public';
        $directory = 'media/' . $collectionName;
        
        // 存储文件
        $path = $file->store($directory, $disk);
        
        // 获取文件信息
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $fileName = $file->getClientOriginalName();
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        
        // 创建媒体记录
        $mediaData = [
            'name' => $name,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => $disk,
            'path' => $path,
            'collection_name' => $collectionName,
            'size' => $size,
            'custom_properties' => $customProperties,
            'uploaded_by' => auth()->id(),
        ];
        
        // 如果是图片，获取尺寸
        if (str_starts_with($mimeType, 'image/')) {
            $fullPath = Storage::disk($disk)->path($path);
            if (file_exists($fullPath)) {
                $imageSize = getimagesize($fullPath);
                if ($imageSize) {
                    $mediaData['width'] = $imageSize[0];
                    $mediaData['height'] = $imageSize[1];
                }
            }
        }
        
        return $this->media()->create($mediaData);
    }

    /**
     * 从路径添加媒体
     */
    protected function addMediaFromPath(string $path, string $collectionName, array $customProperties): Media
    {
        $disk = 'public';
        
        if (!Storage::disk($disk)->exists($path)) {
            throw new \Exception("文件不存在: {$path}");
        }
        
        $mimeType = Storage::disk($disk)->mimeType($path);
        $size = Storage::disk($disk)->size($path);
        $fileName = basename($path);
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        
        $mediaData = [
            'name' => $name,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => $disk,
            'path' => $path,
            'collection_name' => $collectionName,
            'size' => $size,
            'custom_properties' => $customProperties,
            'uploaded_by' => auth()->id(),
        ];
        
        // 如果是图片，获取尺寸
        if (str_starts_with($mimeType, 'image/')) {
            $fullPath = Storage::disk($disk)->path($path);
            if (file_exists($fullPath)) {
                $imageSize = getimagesize($fullPath);
                if ($imageSize) {
                    $mediaData['width'] = $imageSize[0];
                    $mediaData['height'] = $imageSize[1];
                }
            }
        }
        
        return $this->media()->create($mediaData);
    }

    /**
     * 清空特定集合的媒体
     */
    public function clearMediaCollection(string $collectionName = 'default'): void
    {
        $this->media()->where('collection_name', $collectionName)->each(function (Media $media) {
            $media->deleteFile();
            $media->delete();
        });
    }

    /**
     * 删除特定媒体
     */
    public function deleteMedia(int $mediaId): bool
    {
        $media = $this->media()->find($mediaId);
        
        if ($media) {
            $media->deleteFile();
            return $media->delete();
        }
        
        return false;
    }

    /**
     * 检查是否有媒体
     */
    public function hasMedia(string $collectionName = 'default'): bool
    {
        return $this->media()->where('collection_name', $collectionName)->exists();
    }

    /**
     * 获取媒体数量
     */
    public function getMediaCount(string $collectionName = 'default'): int
    {
        return $this->media()->where('collection_name', $collectionName)->count();
    }

    /**
     * 更新媒体顺序
     */
    public function updateMediaOrder(array $mediaIds): void
    {
        foreach ($mediaIds as $order => $mediaId) {
            $this->media()->where('id', $mediaId)->update(['order_column' => $order + 1]);
        }
    }

    /**
     * 启动模型事件
     */
    protected static function bootHasMedia(): void
    {
        // 删除模型时同时删除关联的媒体
        static::deleting(function ($model) {
            $model->media->each(function (Media $media) {
                $media->deleteFile();
                $media->delete();
            });
        });
    }
}
