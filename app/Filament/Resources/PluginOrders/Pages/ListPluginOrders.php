<?php

namespace App\Filament\Resources\PluginOrders\Pages;

use App\Filament\Resources\PluginOrders\PluginOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListPluginOrders extends ListRecords
{
    protected static string $resource = PluginOrderResource::class;
}
