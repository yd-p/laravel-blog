<?php

namespace App\Filament\Resources\CommentResource\Pages;

use App\Enums\CommentStatus;
use App\Filament\Resources\CommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('添加评论'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('全部'),
            
            'pending' => Tab::make('待审核')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::PENDING))
                ->badge(fn () => \App\Models\Comment::where('status', CommentStatus::PENDING)->count())
                ->badgeColor('warning'),
            
            'approved' => Tab::make('已批准')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::APPROVED))
                ->badge(fn () => \App\Models\Comment::where('status', CommentStatus::APPROVED)->count())
                ->badgeColor('success'),
            
            'spam' => Tab::make('垃圾评论')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::SPAM))
                ->badge(fn () => \App\Models\Comment::where('status', CommentStatus::SPAM)->count())
                ->badgeColor('danger'),
            
            'trash' => Tab::make('回收站')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::TRASH))
                ->badge(fn () => \App\Models\Comment::where('status', CommentStatus::TRASH)->count())
                ->badgeColor('gray'),
        ];
    }
}
