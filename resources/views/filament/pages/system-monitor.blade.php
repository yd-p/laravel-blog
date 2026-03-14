<x-filament-panels::page>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.5rem;">

        {{-- 运行环境 --}}
        <div style="background:var(--fi-color-white,#fff);border-radius:0.75rem;box-shadow:0 1px 2px 0 rgb(0 0 0/.05);border:1px solid rgb(0 0 0/.06);overflow:hidden;">
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.5rem;border-bottom:1px solid rgb(0 0 0/.06);">
                <x-filament::icon icon="heroicon-o-code-bracket" style="width:1.25rem;height:1.25rem;color:var(--primary-500,#f59e0b);" />
                <span style="font-size:0.9375rem;font-weight:600;color:rgb(3 7 18);">运行环境</span>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:0.625rem;">
                @foreach ([
                    'PHP 版本'     => $stats['php_version'],
                    'Laravel 版本' => $stats['laravel_version'],
                    '操作系统'     => $stats['server_os'],
                    '运行环境'     => $stats['env'],
                    '时区'         => $stats['timezone'],
                    '服务器时间'   => $stats['server_time'],
                ] as $label => $value)
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.875rem;">
                        <span style="color:rgb(107 114 128);">{{ $label }}</span>
                        <span style="font-weight:500;color:rgb(3 7 18);">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 内存 / CPU --}}
        <div style="background:var(--fi-color-white,#fff);border-radius:0.75rem;box-shadow:0 1px 2px 0 rgb(0 0 0/.05);border:1px solid rgb(0 0 0/.06);overflow:hidden;">
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.5rem;border-bottom:1px solid rgb(0 0 0/.06);">
                <x-filament::icon icon="heroicon-o-cpu-chip" style="width:1.25rem;height:1.25rem;color:var(--primary-500,#f59e0b);" />
                <span style="font-size:0.9375rem;font-weight:600;color:rgb(3 7 18);">内存 / CPU</span>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:0.625rem;">
                @foreach ([
                    '当前内存占用' => $stats['memory_usage'],
                    '内存峰值'     => $stats['memory_peak'],
                    'PHP 内存限制' => $stats['memory_limit'],
                    'CPU 核心数'   => $stats['cpu_cores'],
                    '系统负载'     => $stats['load_avg'],
                    '运行时长'     => $stats['uptime'],
                ] as $label => $value)
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.875rem;">
                        <span style="color:rgb(107 114 128);">{{ $label }}</span>
                        <span style="font-weight:500;color:rgb(3 7 18);">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 磁盘空间 --}}
        <div style="background:var(--fi-color-white,#fff);border-radius:0.75rem;box-shadow:0 1px 2px 0 rgb(0 0 0/.05);border:1px solid rgb(0 0 0/.06);overflow:hidden;">
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.5rem;border-bottom:1px solid rgb(0 0 0/.06);">
                <x-filament::icon icon="heroicon-o-circle-stack" style="width:1.25rem;height:1.25rem;color:var(--primary-500,#f59e0b);" />
                <span style="font-size:0.9375rem;font-weight:600;color:rgb(3 7 18);">磁盘空间</span>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:0.625rem;">
                @foreach ([
                    '总容量'   => $stats['disk_total'],
                    '可用空间' => $stats['disk_free'],
                    '已用比例' => $stats['disk_used_pct'],
                ] as $label => $value)
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.875rem;">
                        <span style="color:rgb(107 114 128);">{{ $label }}</span>
                        <span style="font-weight:500;color:rgb(3 7 18);">{{ $value }}</span>
                    </div>
                @endforeach

                @php $pct = (float) $stats['disk_used_pct']; @endphp
                <div style="margin-top:0.5rem;">
                    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:rgb(156 163 175);margin-bottom:0.25rem;">
                        <span>磁盘使用率</span><span>{{ $stats['disk_used_pct'] }}</span>
                    </div>
                    <div style="width:100%;background:rgb(229 231 235);border-radius:9999px;height:8px;">
                        <div style="height:8px;border-radius:9999px;width:{{ min($pct,100) }}%;background:{{ $pct > 90 ? '#ef4444' : ($pct > 70 ? '#f59e0b' : '#22c55e') }};"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 应用配置 --}}
        <div style="background:var(--fi-color-white,#fff);border-radius:0.75rem;box-shadow:0 1px 2px 0 rgb(0 0 0/.05);border:1px solid rgb(0 0 0/.06);overflow:hidden;">
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.5rem;border-bottom:1px solid rgb(0 0 0/.06);">
                <x-filament::icon icon="heroicon-o-cog-6-tooth" style="width:1.25rem;height:1.25rem;color:var(--primary-500,#f59e0b);" />
                <span style="font-size:0.9375rem;font-weight:600;color:rgb(3 7 18);">应用配置</span>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:0.625rem;">
                @foreach ([
                    '数据库驱动' => $stats['db_connection'],
                    '缓存驱动'   => $stats['cache_driver'],
                    '队列驱动'   => $stats['queue_driver'],
                ] as $label => $value)
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.875rem;">
                        <span style="color:rgb(107 114 128);">{{ $label }}</span>
                        <span style="font-weight:500;color:rgb(3 7 18);">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PHP 扩展（跨两列） --}}
        <div style="background:var(--fi-color-white,#fff);border-radius:0.75rem;box-shadow:0 1px 2px 0 rgb(0 0 0/.05);border:1px solid rgb(0 0 0/.06);overflow:hidden;grid-column:1/-1;">
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.5rem;border-bottom:1px solid rgb(0 0 0/.06);">
                <x-filament::icon icon="heroicon-o-puzzle-piece" style="width:1.25rem;height:1.25rem;color:var(--primary-500,#f59e0b);" />
                <span style="font-size:0.9375rem;font-weight:600;color:rgb(3 7 18);">已加载 PHP 扩展（前 20 个）</span>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;flex-wrap:wrap;gap:0.5rem;">
                @foreach (array_slice(get_loaded_extensions(), 0, 20) as $ext)
                    <span style="display:inline-flex;align-items:center;border-radius:0.375rem;background:rgb(254 243 199);padding:0.25rem 0.625rem;font-size:0.75rem;font-weight:500;color:rgb(146 64 14);border:1px solid rgb(253 230 138);">
                        {{ $ext }}
                    </span>
                @endforeach
            </div>
        </div>

    </div>
</x-filament-panels::page>
