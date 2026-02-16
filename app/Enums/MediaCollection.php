<?php

namespace App\Enums;

enum MediaCollection: string
{
    case DEFAULT = 'default';
    case POSTS = 'posts';
    case PRODUCTS = 'products';
    case AVATARS = 'avatars';
    case BANNERS = 'banners';
    case DOCUMENTS = 'documents';

    /**
     * 获取集合标签
     */
    public function label(): string
    {
        return match($this) {
            self::DEFAULT => '默认',
            self::POSTS => '文章',
            self::PRODUCTS => '产品',
            self::AVATARS => '头像',
            self::BANNERS => '横幅',
            self::DOCUMENTS => '文档',
        };
    }

    /**
     * 获取集合描述
     */
    public function description(): string
    {
        return match($this) {
            self::DEFAULT => '默认集合，用于存储未分类的文件',
            self::POSTS => '文章相关的图片和附件',
            self::PRODUCTS => '产品图片和相关文件',
            self::AVATARS => '用户头像',
            self::BANNERS => '网站横幅和广告图片',
            self::DOCUMENTS => '文档和PDF文件',
        };
    }

    /**
     * 获取集合颜色（用于 Filament）
     */
    public function color(): string
    {
        return match($this) {
            self::DEFAULT => 'gray',
            self::POSTS => 'primary',
            self::PRODUCTS => 'success',
            self::AVATARS => 'info',
            self::BANNERS => 'warning',
            self::DOCUMENTS => 'danger',
        };
    }

    /**
     * 获取集合图标
     */
    public function icon(): string
    {
        return match($this) {
            self::DEFAULT => 'heroicon-o-folder',
            self::POSTS => 'heroicon-o-document-text',
            self::PRODUCTS => 'heroicon-o-shopping-bag',
            self::AVATARS => 'heroicon-o-user-circle',
            self::BANNERS => 'heroicon-o-photo',
            self::DOCUMENTS => 'heroicon-o-document',
        };
    }

    /**
     * 获取所有选项（用于表单）
     */
    public static function options(): array
    {
        return [
            self::DEFAULT->value => self::DEFAULT->label(),
            self::POSTS->value => self::POSTS->label(),
            self::PRODUCTS->value => self::PRODUCTS->label(),
            self::AVATARS->value => self::AVATARS->label(),
            self::BANNERS->value => self::BANNERS->label(),
            self::DOCUMENTS->value => self::DOCUMENTS->label(),
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
     * 获取存储路径
     */
    public function getStoragePath(): string
    {
        return match($this) {
            self::DEFAULT => 'media',
            self::POSTS => 'media/posts',
            self::PRODUCTS => 'media/products',
            self::AVATARS => 'media/avatars',
            self::BANNERS => 'media/banners',
            self::DOCUMENTS => 'media/documents',
        };
    }

    /**
     * 获取最大文件大小（KB）
     */
    public function getMaxSize(): int
    {
        return match($this) {
            self::AVATARS => 2048,      // 2MB
            self::BANNERS => 5120,      // 5MB
            self::DOCUMENTS => 10240,   // 10MB
            default => 10240,           // 10MB
        };
    }

    /**
     * 获取允许的文件类型
     */
    public function getAllowedTypes(): array
    {
        return match($this) {
            self::AVATARS => ['image/*'],
            self::BANNERS => ['image/*'],
            self::DOCUMENTS => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            default => ['image/*', 'video/*', 'audio/*', 'application/pdf'],
        };
    }
}
