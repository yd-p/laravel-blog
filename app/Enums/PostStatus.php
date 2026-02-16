<?php

namespace App\Enums;

enum PostStatus: int
{
    case DRAFT = 1;
    case PUBLISHED = 2;
    case TRASH = 3;

    /**
     * 获取状态标签
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => '草稿',
            self::PUBLISHED => '已发布',
            self::TRASH => '回收站',
        };
    }

    /**
     * 获取状态颜色（用于 Filament）
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'success',
            self::TRASH => 'danger',
        };
    }

    /**
     * 获取状态图标
     */
    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'heroicon-o-pencil',
            self::PUBLISHED => 'heroicon-o-check-circle',
            self::TRASH => 'heroicon-o-trash',
        };
    }

    /**
     * 获取所有选项（用于表单）
     */
    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->label(),
            self::PUBLISHED->value => self::PUBLISHED->label(),
            self::TRASH->value => self::TRASH->label(),
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
     * 从值获取枚举实例
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * 检查是否为草稿
     */
    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * 检查是否已发布
     */
    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * 检查是否在回收站
     */
    public function isTrash(): bool
    {
        return $this === self::TRASH;
    }
}
