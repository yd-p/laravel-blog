<?php

namespace App\Enums;

enum TagStatus: int
{
    case DISABLED = 0;
    case ENABLED = 1;

    /**
     * 获取状态标签
     */
    public function label(): string
    {
        return match($this) {
            self::DISABLED => '禁用',
            self::ENABLED => '启用',
        };
    }

    /**
     * 获取状态颜色（用于 Filament）
     */
    public function color(): string
    {
        return match($this) {
            self::DISABLED => 'danger',
            self::ENABLED => 'success',
        };
    }

    /**
     * 获取状态图标
     */
    public function icon(): string
    {
        return match($this) {
            self::DISABLED => 'heroicon-o-x-circle',
            self::ENABLED => 'heroicon-o-check-circle',
        };
    }

    /**
     * 获取所有选项（用于表单）
     */
    public static function options(): array
    {
        return [
            self::DISABLED->value => self::DISABLED->label(),
            self::ENABLED->value => self::ENABLED->label(),
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
     * 检查是否启用
     */
    public function isEnabled(): bool
    {
        return $this === self::ENABLED;
    }

    /**
     * 检查是否禁用
     */
    public function isDisabled(): bool
    {
        return $this === self::DISABLED;
    }

    /**
     * 转换为布尔值
     */
    public function toBool(): bool
    {
        return $this === self::ENABLED;
    }

    /**
     * 从布尔值创建
     */
    public static function fromBool(bool $value): self
    {
        return $value ? self::ENABLED : self::DISABLED;
    }
}
