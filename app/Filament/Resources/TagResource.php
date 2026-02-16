<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Support\Enums\FontWeight;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = '标签管理';

    protected static ?string $modelLabel = '标签';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('标签信息')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('标签名称')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->label('标签别名')
                                    ->required()
                                    ->maxLength(150)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('用于URL，只能包含字母、数字和连字符'),

                                Forms\Components\ColorPicker::make('color')
                                    ->label('标签颜色')
                                    ->helperText('用于前端显示标签时的颜色'),

                                Forms\Components\Textarea::make('description')
                                    ->label('标签描述')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('状态设置')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('状态')
                                    ->options(\App\Enums\TagStatus::toSelectArray())
                                    ->required()
                                    ->default(\App\Enums\TagStatus::ENABLED->value)
                                    ->native(false),

                                Forms\Components\Placeholder::make('post_count')
                                    ->label('文章数量')
                                    ->content(fn (?Tag $record): string => $record?->post_count ?? '0'),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label('创建时间')
                                    ->content(fn (?Tag $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('更新时间')
                                    ->content(fn (?Tag $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                            ])
                            ->hidden(fn (?Tag $record) => $record === null),
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

                Tables\Columns\TextColumn::make('name')
                    ->label('标签名称')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\ColorColumn::make('color')
                    ->label('颜色'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('别名')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('已复制')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (\App\Enums\TagStatus $state): string => $state->label())
                    ->color(fn (\App\Enums\TagStatus $state): string => $state->color())
                    ->icon(fn (\App\Enums\TagStatus $state): string => $state->icon())
                    ->sortable(),

                Tables\Columns\TextColumn::make('post_count')
                    ->label('文章数')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(\App\Enums\TagStatus::toSelectArray()),

                Tables\Filters\Filter::make('has_posts')
                    ->label('有文章')
                    ->query(fn ($query) => $query->where('post_count', '>', 0)),

                Tables\Filters\Filter::make('no_posts')
                    ->label('无文章')
                    ->query(fn ($query) => $query->where('post_count', '=', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('enable')
                    ->label('启用')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Tag $record) => $record->enable())
                    ->visible(fn (Tag $record) => $record->status !== \App\Enums\TagStatus::ENABLED)
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('disable')
                    ->label('禁用')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Tag $record) => $record->disable())
                    ->visible(fn (Tag $record) => $record->status !== \App\Enums\TagStatus::DISABLED)
                    ->requiresConfirmation(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('enable')
                        ->label('批量启用')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->enable())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('disable')
                        ->label('批量禁用')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->disable())
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('update_count')
                        ->label('更新文章数')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(fn ($records) => $records->each->updatePostCount())
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'view' => Pages\ViewTag::route('/{record}'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
