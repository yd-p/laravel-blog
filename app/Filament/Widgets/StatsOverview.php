<?php

namespace App\Filament\Widgets;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalPosts      = Post::count();
        $publishedPosts  = Post::where('status', PostStatus::PUBLISHED)->count();
        $draftPosts      = Post::where('status', PostStatus::DRAFT)->count();

        $totalComments   = Comment::count();
        $pendingComments = Comment::where('status', CommentStatus::PENDING)->count();

        $totalViews = Post::sum('view_count');

        return [
            Stat::make('文章总数', $totalPosts)
                ->description("已发布 {$publishedPosts} · 草稿 {$draftPosts}")
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart(
                    Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->groupBy('date')
                        ->pluck('count')
                        ->toArray()
                ),

            Stat::make('评论总数', $totalComments)
                ->description("待审核 {$pendingComments} 条")
                ->descriptionIcon('heroicon-o-chat-bubble-left-ellipsis')
                ->color($pendingComments > 0 ? 'warning' : 'success'),

            Stat::make('总浏览量', number_format($totalViews))
                ->description('所有文章累计')
                ->descriptionIcon('heroicon-o-eye')
                ->color('info'),

            Stat::make('用户数', User::count())
                ->description('注册用户')
                ->descriptionIcon('heroicon-o-users')
                ->color('gray'),
        ];
    }
}
