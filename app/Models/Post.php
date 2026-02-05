<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_TRASH = 3;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'thumbnail',
        'status',
        'published_at',
        'view_count',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'author_id',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'status' => 'integer',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'author_id' => 'integer',
    ];

    protected $dates = [
        'published_at',
    ];

    /**
     * 获取分类
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 获取作者
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * 获取文章的所有标签
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag')
            ->withTimestamps();
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => '草稿',
            self::STATUS_PUBLISHED => '已发布',
            self::STATUS_TRASH => '回收站',
            default => '未知',
        };
    }

    /**
     * 获取摘要（如果没有则从内容截取）
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        return Str::limit(strip_tags($this->content), 200);
    }

    /**
     * 获取阅读时长（估算）
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // 假设每分钟阅读200字
    }

    /**
     * 检查是否已发布
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && 
               $this->published_at && 
               $this->published_at->isPast();
    }

    /**
     * 检查是否为草稿
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * 增加阅读量
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * 发布文章
     */
    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /**
     * 撤回发布
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    /**
     * 获取已发布的文章
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * 获取草稿文章
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * 按发布时间排序
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * 按阅读量排序
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * 搜索文章
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('content', 'like', "%{$keyword}%")
              ->orWhere('excerpt', 'like', "%{$keyword}%");
        });
    }

    /**
     * 自动生成slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            // 确保slug唯一
            $originalSlug = $post->slug;
            $count = 1;
            while (static::where('slug', $post->slug)->exists()) {
                $post->slug = $originalSlug . '-' . $count;
                $count++;
            }
        });
    }
}