<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SystemMonitor extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = '系统监控';
    protected static ?string $title = '系统监控';
    protected static string|\UnitEnum|null $navigationGroup = '系统设置';
    protected static ?int $navigationSort = 10;

    public function getView(): string
    {
        return 'filament.pages.system-monitor';
    }

    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = $this->getSystemStats();
    }

    private function getSystemStats(): array
    {
        return [
            'php_version'    => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_os'      => PHP_OS,
            'server_time'    => now()->format('Y-m-d H:i:s'),
            'timezone'       => config('app.timezone'),
            'memory_usage'   => $this->formatBytes(memory_get_usage(true)),
            'memory_peak'    => $this->formatBytes(memory_get_peak_usage(true)),
            'memory_limit'   => ini_get('memory_limit'),
            'disk_total'     => $this->formatBytes(disk_total_space('/')),
            'disk_free'      => $this->formatBytes(disk_free_space('/')),
            'disk_used_pct'  => $this->getDiskUsedPercent(),
            'cpu_cores'      => $this->getCpuCores(),
            'load_avg'       => $this->getLoadAvg(),
            'uptime'         => $this->getUptime(),
            'extensions'     => implode(', ', array_slice(get_loaded_extensions(), 0, 20)),
            'db_connection'  => config('database.default'),
            'cache_driver'   => config('cache.default'),
            'queue_driver'   => config('queue.default'),
            'env'            => app()->environment(),
        ];
    }

    private function formatBytes(int|float $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    private function getDiskUsedPercent(): string
    {
        $total = disk_total_space('/');
        $free  = disk_free_space('/');
        if ($total <= 0) return '0%';
        return round(($total - $free) / $total * 100, 1) . '%';
    }

    private function getCpuCores(): int|string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $cores = shell_exec('nproc 2>/dev/null');
            return $cores ? (int) trim($cores) : '未知';
        }
        if (PHP_OS_FAMILY === 'Darwin') {
            $cores = shell_exec('sysctl -n hw.ncpu 2>/dev/null');
            return $cores ? (int) trim($cores) : '未知';
        }
        return '未知';
    }

    private function getLoadAvg(): string
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return implode(' / ', array_map(fn($v) => round($v, 2), $load));
        }
        return '不支持';
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = shell_exec('uptime -p 2>/dev/null');
            return $uptime ? trim($uptime) : '未知';
        }
        return '未知';
    }
}
