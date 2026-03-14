<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'collection_name',
        'size',
        'custom_properties',
        'responsive_images',
        'order_column',
        'width',
        'height',
        'model_type',
        'model_id',
        'uploaded_by',
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'responsive_images' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'order_column' => 'integer',
    ];

    /**
     * 关联到可附加媒体的模型
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 上传者
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * 获取完整URL
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * 获取临时URL（用于私有文件）
     */
    public function getTemporaryUrl(int $minutes = 60): string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, now()->addMinutes($minutes));
    }

    /**
     * 获取人类可读的文件大小
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 检查是否为图片
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * 检查是否为视频
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * 检查是否为音频
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * 检查是否为文档
     */
    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        
        return in_array($this->mime_type, $documentMimes);
    }

    /**
     * 获取文件类型
     */
    public function getTypeAttribute(): string
    {
        if ($this->isImage()) return 'image';
        if ($this->isVideo()) return 'video';
        if ($this->isAudio()) return 'audio';
        if ($this->isDocument()) return 'document';
        return 'other';
    }

    /**
     * 获取文件扩展名
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * 删除文件
     */
    public function deleteFile(): bool
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            return Storage::disk($this->disk)->delete($this->path);
        }
        return true;
    }

    /**
     * 作用域：按类型筛选
     */
    public function scopeOfType($query, string $type)
    {
        return match ($type) {
            'image' => $query->where('mime_type', 'like', 'image/%'),
            'video' => $query->where('mime_type', 'like', 'video/%'),
            'audio' => $query->where('mime_type', 'like', 'audio/%'),
            'document' => $query->whereIn('mime_type', [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]),
            default => $query,
        };
    }

    /**
     * 作用域：按集合筛选
     */
    public function scopeInCollection($query, string $collection)
    {
        return $query->where('collection_name', $collection);
    }

    /**
     * 作用域：按上传者筛选
     */
    public function scopeUploadedBy($query, int $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * 启动模型事件
     */
    protected static function boot()
    {
        parent::boot();

        // 创建时自动填充文件信息
        static::creating(function ($media) {
            // 设置默认磁盘
            if (!$media->disk) {
                $media->disk = 'public';
            }
            
            // 如果 path 存在但 file_name 不存在，从 path 提取
            if ($media->path && !$media->file_name) {
                $media->file_name = basename($media->path);
            }
            
            // 尝试获取文件信息（如果文件已经存在）
            if ($media->path && Storage::disk($media->disk)->exists($media->path)) {
                try {
                    // 设置 MIME 类型
                    if (!$media->mime_type) {
                        $media->mime_type = Storage::disk($media->disk)->mimeType($media->path);
                    }
                    
                    // 设置文件大小
                    if (!$media->size) {
                        $media->size = Storage::disk($media->disk)->size($media->path);
                    }
                    
                    // 如果是图片，获取尺寸
                    if (!$media->width && !$media->height && $media->mime_type && str_starts_with($media->mime_type, 'image/')) {
                        try {
                            $fullPath = Storage::disk($media->disk)->path($media->path);
                            $imageSize = getimagesize($fullPath);
                            if ($imageSize) {
                                $media->width = $imageSize[0];
                                $media->height = $imageSize[1];
                            }
                        } catch (\Exception $e) {
                            // 忽略图片尺寸获取错误
                        }
                    }
                } catch (\Exception $e) {
                    // 如果无法获取文件信息，继续保存（文件可能还在临时目录）
                }
            }
        });

        // 删除模型时同时删除文件
        static::deleting(function ($media) {
            if ($media->isForceDeleting()) {
                $media->deleteFile();
            }
        });
    }
}
