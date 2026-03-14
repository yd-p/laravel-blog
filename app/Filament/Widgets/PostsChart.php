<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PostsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = '近 30 天文章发布趋势';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $counts = Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label'           => '新增文章',
                    'data'            => $days->map(fn ($d) => $counts[$d] ?? 0)->values()->toArray(),
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('m/d'))->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
