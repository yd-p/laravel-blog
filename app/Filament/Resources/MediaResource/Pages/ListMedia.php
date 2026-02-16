<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('上传文件'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('全部'),
            
            'images' => Tab::make('图片')
                ->modifyQueryUsing(fn (Builder $query) => $query->ofType('image'))
                ->badge(fn () => \App\Models\Media::ofType('image')->count()),
            
            'videos' => Tab::make('视频')
                ->modifyQueryUsing(fn (Builder $query) => $query->ofType('video'))
                ->badge(fn () => \App\Models\Media::ofType('video')->count()),
            
            'audio' => Tab::make('音频')
                ->modifyQueryUsing(fn (Builder $query) => $query->ofType('audio'))
                ->badge(fn () => \App\Models\Media::ofType('audio')->count()),
            
            'documents' => Tab::make('文档')
                ->modifyQueryUsing(fn (Builder $query) => $query->ofType('document'))
                ->badge(fn () => \App\Models\Media::ofType('document')->count()),
        ];
    }
}
