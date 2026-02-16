<?php

namespace App\Enums;

enum CommentStatus: int
{
    case PENDING = 0;      // 待审核
    case APPROVED = 1;     // 已批准
    case SPAM = 2;         // 垃圾评论
    case TRASH = 3;        // 回收站

    /**
     * 获取状态标签
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => '待审核',
            self::APPROVED => '已批准',
            self::SPAM => '垃圾评论',
            self::TRASH => '回收站',
        };
    }

    /**
     * 获取状态颜色（用于 Filament）
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::SPAM => 'danger',
            self::TRASH => 'gray',
        };
    }

    /**
     * 获取状态图标
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check-circle',
            self::SPAM => 'heroicon-o-exclamation-triangle',
            self::TRASH => 'heroicon-o-trash',
        };
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
     * 检查是否待审核
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * 检查是否已批准
     */
    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    /**
     * 检查是否为垃圾评论
     */
    public function isSpam(): bool
    {
        return $this === self::SPAM;
    }

    /**
     * 检查是否在回收站
     */
    public function isTrash(): bool
    {
        return $this === self::TRASH;
    }
}
