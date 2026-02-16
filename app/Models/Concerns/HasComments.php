<?php

namespace App\Models\Concerns;

use App\Enums\CommentStatus;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    /**
     * 获取所有评论
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * 获取已批准的评论
     */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->where('status', CommentStatus::APPROVED)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 获取待审核的评论
     */
    public function pendingComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->where('status', CommentStatus::PENDING)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 获取顶级评论（不包括回复）
     */
    public function topLevelComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->whereNull('parent_id')
            ->where('status', CommentStatus::APPROVED)
            ->with('replies')
            ->orderBy('created_at', 'desc');
    }

    /**
     * 获取评论数量
     */
    public function getCommentCountAttribute(): int
    {
        return $this->approvedComments()->count();
    }

    /**
     * 获取待审核评论数量
     */
    public function getPendingCommentCountAttribute(): int
    {
        return $this->pendingComments()->count();
    }

    /**
     * 检查是否有评论
     */
    public function hasComments(): bool
    {
        return $this->approvedComments()->exists();
    }

    /**
     * 检查是否允许评论
     */
    public function allowsComments(): bool
    {
        // 可以在这里添加更多逻辑，比如检查文章状态、发布时间等
        return true;
    }

    /**
     * 添加评论
     */
    public function addComment(array $data): Comment
    {
        return $this->comments()->create(array_merge($data, [
            'author_ip' => request()->ip(),
            'author_user_agent' => request()->userAgent(),
            'status' => CommentStatus::PENDING->value,
        ]));
    }

    /**
     * 获取最新评论
     */
    public function getLatestComments(int $limit = 5)
    {
        return $this->approvedComments()
            ->limit($limit)
            ->get();
    }

    /**
     * 获取评论统计
     */
    public function getCommentStats(): array
    {
        return [
            'total' => $this->comments()->count(),
            'approved' => $this->approvedComments()->count(),
            'pending' => $this->pendingComments()->count(),
            'spam' => $this->comments()->where('status', CommentStatus::SPAM)->count(),
        ];
    }
}
