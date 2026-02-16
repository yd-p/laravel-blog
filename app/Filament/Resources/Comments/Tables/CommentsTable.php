<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('author_avatar')
                    ->label('头像')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('https://www.gravatar.com/avatar/?d=mp'),

                TextColumn::make('author_display_name')
                    ->label('评论者')
                    ->searchable(['author_name', 'author_email'])
                    ->sortable()
                    ->description(fn (Comment $record): string => $record->author_email),

                TextColumn::make('content')
                    ->label('内容')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn (Comment $record): string => $record->content)
                    ->wrap(),

                TextColumn::make('post.title')
                    ->label('文章')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->url(fn (Comment $record) => $record->post ? route('posts.show', $record->post) : null)
                    ->openUrlInNewTab(),

                TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (CommentStatus $state) => $state->label())
                    ->color(fn (CommentStatus $state) => $state->color())
                    ->icon(fn (CommentStatus $state) => $state->icon())
                    ->sortable(),

                TextColumn::make('reply_count')
                    ->label('回复')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('评论时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('author_ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('状态')
                    ->options(CommentStatus::toSelectArray()),

                SelectFilter::make('post')
                    ->label('文章')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),

                Filter::make('is_reply')
                    ->label('仅回复')
                    ->query(fn (Builder $query) => $query->whereNotNull('parent_id')),

                Filter::make('top_level')
                    ->label('仅顶级评论')
                    ->query(fn (Builder $query) => $query->whereNull('parent_id')),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('评论日期从'),
                        DatePicker::make('created_until')
                            ->label('评论日期到'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                TrashedFilter::make()
                    ->label('显示已删除数据'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('批准')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Comment $record) => $record->approve())
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::APPROVED)
                    ->requiresConfirmation(),

                Action::make('spam')
                    ->label('垃圾')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->action(fn (Comment $record) => $record->markAsSpam())
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::SPAM)
                    ->requiresConfirmation(),

                Action::make('reply')
                    ->label('回复')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('info')
                    ->url(fn (Comment $record) => CommentResource::getUrl('create', [
                        'parent_id' => $record->id,
                        'post_id' => $record->post_id,
                    ])),

                ViewAction::make()
                    ->label('查看'),

                EditAction::make()
                    ->label('编辑'),

                DeleteAction::make()
                    ->label('删除'),

                ForceDeleteAction::make()
                    ->label('强制删除'),

                RestoreAction::make()
                    ->label('恢复'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('批准')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->approve();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('spam')
                        ->label('标记为垃圾')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->markAsSpam();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    DeleteBulkAction::make()
                        ->label('删除'),

                    ForceDeleteBulkAction::make()
                        ->label('强制删除'),

                    RestoreBulkAction::make()
                        ->label('恢复'),
                ])->label('批量操作'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
