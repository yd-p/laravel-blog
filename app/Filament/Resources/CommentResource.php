<?php

namespace App\Filament\Resources;

use App\Enums\CommentStatus;
use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = '评论管理';

    protected static ?string $modelLabel = '评论';

    protected static ?string $pluralModelLabel = '评论';

    protected static ?string $navigationGroup = '内容管理';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('评论信息')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->label('所属文章')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('parent_id')
                            ->label('父评论')
                            ->relationship('parent', 'id')
                            ->searchable()
                            ->preload()
                            ->placeholder('无（顶级评论）')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                substr($record->content, 0, 50) . '...'
                            ),

                        Forms\Components\Select::make('status')
                            ->label('状态')
                            ->options(CommentStatus::toSelectArray())
                            ->required()
                            ->default(CommentStatus::PENDING->value)
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('评论者信息')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('关联用户')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('游客评论'),

                        Forms\Components\TextInput::make('author_name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('author_email')
                            ->label('邮箱')
                            ->email()
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('author_url')
                            ->label('网址')
                            ->url()
                            ->maxLength(200),

                        Forms\Components\TextInput::make('author_ip')
                            ->label('IP地址')
                            ->disabled()
                            ->maxLength(45),

                        Forms\Components\Textarea::make('author_user_agent')
                            ->label('用户代理')
                            ->disabled()
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('评论内容')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('内容')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('审核信息')
                    ->schema([
                        Forms\Components\Select::make('approved_by')
                            ->label('审核人')
                            ->relationship('approvedBy', 'name')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('审核时间')
                            ->disabled(),

                        Forms\Components\TextInput::make('karma')
                            ->label('评分')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('reply_count')
                            ->label('回复数')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('author_avatar')
                    ->label('头像')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('https://www.gravatar.com/avatar/?d=mp'),

                Tables\Columns\TextColumn::make('author_display_name')
                    ->label('评论者')
                    ->searchable(['author_name', 'author_email'])
                    ->sortable()
                    ->description(fn (Comment $record): string => $record->author_email),

                Tables\Columns\TextColumn::make('content')
                    ->label('内容')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn (Comment $record): string => $record->content)
                    ->wrap(),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('文章')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->url(fn (Comment $record) => $record->post ? route('posts.show', $record->post) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (CommentStatus $state) => $state->label())
                    ->color(fn (CommentStatus $state) => $state->color())
                    ->icon(fn (CommentStatus $state) => $state->icon())
                    ->sortable(),

                Tables\Columns\TextColumn::make('reply_count')
                    ->label('回复')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('评论时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('author_ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(CommentStatus::toSelectArray()),

                Tables\Filters\SelectFilter::make('post')
                    ->label('文章')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('is_reply')
                    ->label('仅回复')
                    ->query(fn (Builder $query) => $query->whereNotNull('parent_id')),

                Tables\Filters\Filter::make('top_level')
                    ->label('仅顶级评论')
                    ->query(fn (Builder $query) => $query->whereNull('parent_id')),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('评论日期从'),
                        Forms\Components\DatePicker::make('created_until')
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

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('批准')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Comment $record) => $record->approve())
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::APPROVED)
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('spam')
                    ->label('垃圾')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->action(fn (Comment $record) => $record->markAsSpam())
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::SPAM)
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('reply')
                    ->label('回复')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('info')
                    ->url(fn (Comment $record) => CommentResource::getUrl('create', [
                        'parent_id' => $record->id,
                        'post_id' => $record->post_id,
                    ])),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('批准')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->approve();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('spam')
                        ->label('标记为垃圾')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->markAsSpam();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'view' => Pages\ViewComment::route('/{record}'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', CommentStatus::PENDING)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
