<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'author_url',
        'author_ip',
        'author_user_agent',
        'content',
        'status',
        'type',
        'approved_by',
        'approved_at',
        'karma',
        'reply_count',
    ];

    protected $casts = [
        'post_id' => 'integer',
        'parent_id' => 'integer',
        'user_id' => 'integer',
        'status' => CommentStatus::class,
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'karma' => 'integer',
        'reply_count' => 'integer',
    ];

    /**
     * 关联文章
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * 关联用户（评论者）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联审核人
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 父评论
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * 子评论（回复）
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * 所有子评论（递归）
     */
    public function allReplies(): HasMany
    {
        return $this->replies()->with('allReplies');
    }

    /**
     * 获取评论者显示名称
     */
    public function getAuthorDisplayNameAttribute(): string
    {
        return $this->user ? $this->user->name : $this->author_name;
    }

    /**
     * 获取评论者头像
     */
    public function getAuthorAvatarAttribute(): string
    {
        if ($this->user && $this->user->avatar) {
            return $this->user->avatar;
        }
        
        // 使用 Gravatar
        $hash = md5(strtolower(trim($this->author_email)));
        return "https://www.gravatar.com/avatar/{$hash}?s=80&d=mp";
    }

    /**
     * 检查是否为顶级评论
     */
    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * 检查是否为回复
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * 批准评论
     */
    public function approve(?int $approvedBy = null): void
    {
        $this->update([
            'status' => CommentStatus::APPROVED,
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * 标记为垃圾评论
     */
    public function markAsSpam(): void
    {
        $this->update(['status' => CommentStatus::SPAM]);
    }

    /**
     * 移至回收站
     */
    public function moveToTrash(): void
    {
        $this->update(['status' => CommentStatus::TRASH]);
    }

    /**
     * 恢复评论
     */
    public function restore(): void
    {
        $this->update(['status' => CommentStatus::PENDING]);
    }

    /**
     * 更新回复数量
     */
    public function updateReplyCount(): void
    {
        $this->reply_count = $this->replies()
            ->where('status', CommentStatus::APPROVED)
            ->count();
        $this->save();
    }

    /**
     * 作用域：已批准的评论
     */
    public function scopeApproved($query)
    {
        return $query->where('status', CommentStatus::APPROVED);
    }

    /**
     * 作用域：待审核的评论
     */
    public function scopePending($query)
    {
        return $query->where('status', CommentStatus::PENDING);
    }

    /**
     * 作用域：垃圾评论
     */
    public function scopeSpam($query)
    {
        return $query->where('status', CommentStatus::SPAM);
    }

    /**
     * 作用域：顶级评论
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * 作用域：回复
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * 作用域：按文章筛选
     */
    public function scopeForPost($query, int $postId)
    {
        return $query->where('post_id', $postId);
    }

    /**
     * 作用域：最新评论
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * 启动模型事件
     */
    protected static function boot()
    {
        parent::boot();

        // 创建评论时
        static::created(function ($comment) {
            // 更新父评论的回复数
            if ($comment->parent_id) {
                $parent = Comment::find($comment->parent_id);
                if ($parent) {
                    $parent->updateReplyCount();
                }
            }
        });

        // 删除评论时
        static::deleting(function ($comment) {
            // 删除所有回复
            $comment->replies()->delete();
            
            // 更新父评论的回复数
            if ($comment->parent_id) {
                $parent = Comment::find($comment->parent_id);
                if ($parent) {
                    $parent->updateReplyCount();
                }
            }
        });

        // 状态改变时
        static::updated(function ($comment) {
            if ($comment->isDirty('status') && $comment->parent_id) {
                $parent = Comment::find($comment->parent_id);
                if ($parent) {
                    $parent->updateReplyCount();
                }
            }
        });
    }
}
