<?php

namespace App\Enums;

enum MediaType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case OTHER = 'other';

    /**
     * 获取类型标签
     */
    public function label(): string
    {
        return match($this) {
            self::IMAGE => '图片',
            self::VIDEO => '视频',
            self::AUDIO => '音频',
            self::DOCUMENT => '文档',
            self::OTHER => '其他',
        };
    }

    /**
     * 获取类型颜色（用于 Filament）
     */
    public function color(): string
    {
        return match($this) {
            self::IMAGE => 'success',
            self::VIDEO => 'warning',
            self::AUDIO => 'info',
            self::DOCUMENT => 'primary',
            self::OTHER => 'gray',
        };
    }

    /**
     * 获取类型图标
     */
    public function icon(): string
    {
        return match($this) {
            self::IMAGE => 'heroicon-o-photo',
            self::VIDEO => 'heroicon-o-film',
            self::AUDIO => 'heroicon-o-musical-note',
            self::DOCUMENT => 'heroicon-o-document-text',
            self::OTHER => 'heroicon-o-document',
        };
    }

    /**
     * 获取所有选项（用于表单）
     */
    public static function options(): array
    {
        return [
            self::IMAGE->value => self::IMAGE->label(),
            self::VIDEO->value => self::VIDEO->label(),
            self::AUDIO->value => self::AUDIO->label(),
            self::DOCUMENT->value => self::DOCUMENT->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }

    /**
     * 获取所有选项（Filament Select 格式）
     */
    public static function toSelectArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    /**
     * 从 MIME 类型判断媒体类型
     */
    public static function fromMimeType(string $mimeType): self
    {
        if (str_starts_with($mimeType, 'image/')) {
            return self::IMAGE;
        }

        if (str_starts_with($mimeType, 'video/')) {
            return self::VIDEO;
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return self::AUDIO;
        }

        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        if (in_array($mimeType, $documentMimes)) {
            return self::DOCUMENT;
        }

        return self::OTHER;
    }

    /**
     * 获取 MIME 类型模式
     */
    public function getMimePattern(): string
    {
        return match($this) {
            self::IMAGE => 'image/*',
            self::VIDEO => 'video/*',
            self::AUDIO => 'audio/*',
            self::DOCUMENT => 'application/*',
            self::OTHER => '*/*',
        };
    }

    /**
     * 获取文件扩展名列表
     */
    public function getExtensions(): array
    {
        return match($this) {
            self::IMAGE => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            self::VIDEO => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
            self::AUDIO => ['mp3', 'wav', 'ogg', 'flac', 'aac'],
            self::DOCUMENT => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            self::OTHER => [],
        };
    }
}
