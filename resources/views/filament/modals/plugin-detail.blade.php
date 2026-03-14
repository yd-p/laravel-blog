@php
    $rating = $plugin['rating'] ?? 0;
    $fullStars = floor($rating);
    $author = is_array($plugin['author'] ?? null) ? ($plugin['author']['name'] ?? '') : ($plugin['author'] ?? '');
    $authorUrl = is_array($plugin['author'] ?? null) ? ($plugin['author']['url'] ?? '') : '';
    $tags = $plugin['tags'] ?? [];
    $screenshots = $plugin['screenshots'] ?? [];
    $price = (int)($plugin['price'] ?? 0);
    $isFree = $price === 0;
@endphp

<div style="font-size:0.875rem;color:rgb(55 65 81);">

    {{-- 封面图 --}}
    @if(!empty($plugin['cover']))
        <div style="margin-bottom:1.25rem;border-radius:0.5rem;overflow:hidden;height:200px;">
            <img src="{{ $plugin['cover'] }}" alt="{{ $plugin['name'] ?? '' }}"
                 style="width:100%;height:100%;object-fit:cover;">
        </div>
    @endif

    {{-- 基本信息 --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1.25rem;padding:1rem;background:rgb(249 250 251);border-radius:0.5rem;">
        @if(!empty($plugin['version']))
            <div>
                <div style="font-size:0.75rem;color:rgb(107 114 128);margin-bottom:0.125rem;">版本</div>
                <div style="font-weight:500;">v{{ $plugin['version'] }}</div>
            </div>
        @endif
        @if($author)
            <div>
                <div style="font-size:0.75rem;color:rgb(107 114 128);margin-bottom:0.125rem;">作者</div>
                <div style="font-weight:500;">
                    @if($authorUrl)
                        <a href="{{ $authorUrl }}" target="_blank" style="color:rgb(99 102 241);">{{ $author }}</a>
                    @else
                        {{ $author }}
                    @endif
                </div>
            </div>
        @endif
        @if(!empty($plugin['downloads']))
            <div>
                <div style="font-size:0.75rem;color:rgb(107 114 128);margin-bottom:0.125rem;">下载量</div>
                <div style="font-weight:500;">{{ number_format($plugin['downloads']) }}</div>
            </div>
        @endif
        @if($rating > 0)
            <div>
                <div style="font-size:0.75rem;color:rgb(107 114 128);margin-bottom:0.125rem;">评分</div>
                <div style="display:flex;align-items:center;gap:0.25rem;">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="color:{{ $i <= $fullStars ? 'rgb(251 191 36)' : 'rgb(209 213 219)' }};">★</span>
                    @endfor
                    <span style="font-weight:500;margin-left:0.125rem;">{{ number_format($rating, 1) }}</span>
                </div>
            </div>
        @endif
        @if(!empty($plugin['requires']))
            <div>
                <div style="font-size:0.75rem;color:rgb(107 114 128);margin-bottom:0.125rem;">要求版本</div>
                <div style="font-weight:500;">{{ $plugin['requires'] }}</div>
            </div>
        @endif
        @if(!empty($plugin['homepage']))
            <div>
                <div style="font-size:0.75rem;color:rgb(107 114 128);margin-bottom:0.125rem;">主页</div>
                <a href="{{ $plugin['homepage'] }}" target="_blank" style="color:rgb(99 102 241);font-weight:500;word-break:break-all;">访问主页</a>
            </div>
        @endif
    </div>

    {{-- 描述 --}}
    @if(!empty($plugin['description']))
        <div style="margin-bottom:1.25rem;">
            <div style="font-size:0.75rem;font-weight:600;color:rgb(107 114 128);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">插件描述</div>
            <p style="line-height:1.7;color:rgb(55 65 81);">{{ $plugin['description'] }}</p>
        </div>
    @endif

    {{-- 标签 --}}
    @if(count($tags) > 0)
        <div style="margin-bottom:1.25rem;">
            <div style="font-size:0.75rem;font-weight:600;color:rgb(107 114 128);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">标签</div>
            <div style="display:flex;flex-wrap:wrap;gap:0.375rem;">
                @foreach($tags as $tag)
                    <span style="background:rgb(238 242 255);color:rgb(79 70 229);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 截图 --}}
    @if(count($screenshots) > 0)
        <div style="margin-bottom:1.25rem;">
            <div style="font-size:0.75rem;font-weight:600;color:rgb(107 114 128);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">截图预览</div>
            <div style="display:flex;gap:0.5rem;overflow-x:auto;padding-bottom:0.5rem;">
                @foreach($screenshots as $shot)
                    <img src="{{ $shot }}" alt="截图"
                         style="height:120px;border-radius:0.375rem;border:1px solid rgb(229 231 235);flex-shrink:0;cursor:pointer;"
                         onclick="window.open('{{ $shot }}','_blank')">
                @endforeach
            </div>
        </div>
    @endif

    {{-- 更新日志 --}}
    @if(!empty($plugin['changelog']))
        <div>
            <div style="font-size:0.75rem;font-weight:600;color:rgb(107 114 128);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">更新日志</div>
            <pre style="background:rgb(249 250 251);border:1px solid rgb(229 231 235);border-radius:0.5rem;padding:0.75rem;font-size:0.8125rem;line-height:1.6;white-space:pre-wrap;word-break:break-word;max-height:160px;overflow-y:auto;">{{ $plugin['changelog'] }}</pre>
        </div>
    @endif

    {{-- 已安装提示 --}}
    @if($installed)
        <div style="margin-top:1.25rem;display:flex;align-items:center;gap:0.5rem;padding:0.75rem 1rem;background:rgb(240 253 244);border:1px solid rgb(187 247 208);border-radius:0.5rem;color:rgb(22 101 52);">
            <x-filament::icon icon="heroicon-o-check-circle" style="width:1.125rem;height:1.125rem;flex-shrink:0;" />
            <span style="font-size:0.875rem;font-weight:500;">此插件已安装在本系统中</span>
        </div>
    @elseif(!$isFree && !$purchased)
        {{-- 价格展示 --}}
        <div style="margin-top:1.25rem;display:flex;align-items:center;justify-content:space-between;padding:1rem;background:rgb(255 247 237);border:1px solid rgb(254 215 170);border-radius:0.5rem;">
            <div>
                <div style="font-size:0.75rem;color:rgb(154 52 18);margin-bottom:0.125rem;">付费插件</div>
                <div style="font-size:1.5rem;font-weight:700;color:rgb(234 88 12);">¥{{ number_format($price / 100, 2) }}</div>
            </div>
            <div style="font-size:0.8125rem;color:rgb(154 52 18);">购买后永久使用</div>
        </div>
    @elseif(!$isFree && $purchased)
        <div style="margin-top:1.25rem;display:flex;align-items:center;gap:0.5rem;padding:0.75rem 1rem;background:rgb(239 246 255);border:1px solid rgb(191 219 254);border-radius:0.5rem;color:rgb(29 78 216);">
            <x-filament::icon icon="heroicon-o-check-badge" style="width:1.125rem;height:1.125rem;flex-shrink:0;" />
            <span style="font-size:0.875rem;font-weight:500;">已购买，可直接安装</span>
        </div>
    @else
        <div style="margin-top:1.25rem;display:flex;align-items:center;gap:0.5rem;padding:0.75rem 1rem;background:rgb(240 253 244);border:1px solid rgb(187 247 208);border-radius:0.5rem;color:rgb(22 101 52);">
            <x-filament::icon icon="heroicon-o-gift" style="width:1.125rem;height:1.125rem;flex-shrink:0;" />
            <span style="font-size:0.875rem;font-weight:500;">免费插件，可直接安装</span>
        </div>
    @endif
</div>
