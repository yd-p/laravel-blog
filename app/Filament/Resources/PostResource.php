<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Support\Enums\FontWeight;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = '文章管理';

    protected static ?string $modelLabel = '文章';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('文章内容')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('文章标题')
                                    ->required()
                                    ->maxLength(200)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->label('文章别名')
                                    ->required()
                                    ->maxLength(250)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('用于URL，只能包含字母、数字和连字符'),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label('文章摘要')
                                    ->rows(3)
                                    ->helperText('简短描述文章内容，用于列表页显示')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('content')
                                    ->label('文章内容')
                                    ->required()
                                    ->fileAttachmentsDirectory('posts/attachments')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('SEO设置')
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->label('SEO标题')
                                    ->maxLength(200),

                                Forms\Components\TextInput::make('seo_keywords')
                                    ->label('SEO关键词')
                                    ->maxLength(200)
                                    ->helperText('多个关键词用逗号分隔'),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('SEO描述')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('发布设置')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('状态')
                                    ->options([
                                        Post::STATUS_DRAFT => '草稿',
                                        Post::STATUS_PUBLISHED => '已发布',
                                        Post::STATUS_TRASH => '回收站',
                                    ])
                                    ->required()
                                    ->default(Post::STATUS_DRAFT)
                                    ->native(false),

                                Forms\Components\Select::make('category_id')
                                    ->label('分类')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('分类名称')
                                            ->required(),
                                        Forms\Components\TextInput::make('slug')
                                            ->label('分类别名')
                                            ->required(),
                                    ]),

                                Forms\Components\Select::make('tags')
                                    ->label('标签')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('标签名称')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('slug')
                                            ->label('标签别名')
                                            ->required()
                                            ->maxLength(150),
                                        Forms\Components\ColorPicker::make('color')
                                            ->label('标签颜色'),
                                    ])
                                    ->helperText('可以选择多个标签，或创建新标签'),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('发布时间')
                                    ->native(false)
                                    ->helperText('留空则使用当前时间'),

                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('缩略图')
                                    ->image()
                                    ->directory('posts/thumbnails')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(2048),
                            ]),

                        Forms\Components\Section::make('文章信息')
                            ->schema([
                                Forms\Components\Placeholder::make('author')
                                    ->label('作者')
                                    ->content(fn (?Post $record): string => $record?->author?->name ?? auth()->user()->name),

                                Forms\Components\Placeholder::make('view_count')
                                    ->label('阅读量')
                                    ->content(fn (?Post $record): string => $record?->view_count ?? '0'),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label('创建时间')
                                    ->content(fn (?Post $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('更新时间')
                                    ->content(fn (?Post $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                            ])
                            ->hidden(fn (?Post $record) => $record === null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('分类')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('作者')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('状态')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        Post::STATUS_DRAFT => '草稿',
                        Post::STATUS_PUBLISHED => '已发布',
                        Post::STATUS_TRASH => '回收站',
                        default => '未知',
                    })
                    ->colors([
                        'warning' => Post::STATUS_DRAFT,
                        'success' => Post::STATUS_PUBLISHED,
                        'danger' => Post::STATUS_TRASH,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('阅读量')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('发布时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        Post::STATUS_DRAFT => '草稿',
                        Post::STATUS_PUBLISHED => '已发布',
                        Post::STATUS_TRASH => '回收站',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('分类')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label('发布开始日期'),
                        Forms\Components\DatePicker::make('published_until')
                            ->label('发布结束日期'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['published_from'], fn ($query, $date) => $query->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn ($query, $date) => $query->whereDate('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label('发布')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->action(fn (Post $record) => $record->publish())
                    ->visible(fn (Post $record) => $record->status !== Post::STATUS_PUBLISHED)
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('批量发布')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(fn ($records) => $records->each->publish())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('draft')
                        ->label('设为草稿')
                        ->icon('heroicon-o-document')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => Post::STATUS_DRAFT]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
