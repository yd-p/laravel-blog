<?php

namespace App\Models;

use App\Enums\TagStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'post_count',
        'status',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'status' => TagStatus::class,
        'post_count' => 'integer',
    ];

    /**
     * 获取标签下的所有文章
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag')
            ->withTimestamps();
    }

    /**
     * 获取已发布的文章
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()
            ->where('status', Post::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * 更新文章数量
     */
    public function updatePostCount(): void
    {
        $this->post_count = $this->publishedPosts()->count();
        $this->save();
    }

    /**
     * 启用标签
     */
    public function enable(): void
    {
        $this->update(['status' => self::STATUS_ENABLED]);
    }

    /**
     * 禁用标签
     */
    public function disable(): void
    {
        $this->update(['status' => self::STATUS_DISABLED]);
    }

    /**
     * 检查是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 作用域：只查询启用的标签
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 作用域：按文章数量排序
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->where('post_count', '>', 0)
            ->orderBy('post_count', 'desc')
            ->limit($limit);
    }
}
