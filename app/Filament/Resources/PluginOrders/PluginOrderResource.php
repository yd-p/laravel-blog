<?php

namespace App\Filament\Resources\PluginOrders;

use App\Filament\Resources\PluginOrders\Pages\ListPluginOrders;
use App\Filament\Resources\PluginOrders\Tables\PluginOrdersTable;
use App\Models\PluginOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

class PluginOrderResource extends Resource
{
    protected static ?string $model = PluginOrder::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = '插件订单';
    protected static string|\UnitEnum|null $navigationGroup = '系统设置';
    protected static ?int $navigationSort = 32;
    protected static ?string $modelLabel = '订单';
    protected static ?string $pluralModelLabel = '插件订单';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return PluginOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPluginOrders::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
