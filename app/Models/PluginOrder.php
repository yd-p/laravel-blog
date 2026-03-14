<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginOrder extends Model
{
    protected $fillable = [
        'order_no', 'user_id', 'plugin_id', 'plugin_name', 'plugin_folder',
        'plugin_version', 'amount', 'currency', 'status', 'payment_method',
        'payment_trade_no', 'download_url', 'paid_at', 'payment_raw',
    ];

    protected $casts = [
        'paid_at'     => 'datetime',
        'payment_raw' => 'array',
        'amount'      => 'integer',
        'status'      => 'integer',
    ];

    // 状态常量
    const STATUS_PENDING  = 0;
    const STATUS_PAID     = 1;
    const STATUS_REFUNDED = 2;
    const STATUS_CANCELLED = 3;

    public static function statusLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING   => '待支付',
            self::STATUS_PAID      => '已支付',
            self::STATUS_REFUNDED  => '已退款',
            self::STATUS_CANCELLED => '已取消',
            default                => '未知',
        };
    }

    public static function statusColor(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING   => 'warning',
            self::STATUS_PAID      => 'success',
            self::STATUS_REFUNDED  => 'info',
            self::STATUS_CANCELLED => 'danger',
            default                => 'gray',
        };
    }

    /** 金额（元） */
    public function getAmountYuanAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 生成唯一订单号 */
    public static function generateOrderNo(): string
    {
        return 'PLG' . date('YmdHis') . strtoupper(substr(uniqid(), -6));
    }
}
