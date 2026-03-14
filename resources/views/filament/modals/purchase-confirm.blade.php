@php
    $price = ($plugin['price'] ?? 0);
    $priceYuan = number_format($price / 100, 2);
@endphp

<div style="padding:0.25rem 0 1rem;font-size:0.875rem;color:rgb(55 65 81);">
    <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:rgb(249 250 251);border-radius:0.5rem;margin-bottom:1.25rem;">
        <div style="width:3rem;height:3rem;background:linear-gradient(135deg,rgb(238 242 255),rgb(224 231 255));border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <x-filament::icon icon="heroicon-o-puzzle-piece" style="width:1.5rem;height:1.5rem;color:rgb(99 102 241);" />
        </div>
        <div>
            <div style="font-weight:600;color:rgb(17 24 39);">{{ $plugin['name'] ?? '' }}</div>
            @if(!empty($plugin['version']))
                <div style="font-size:0.75rem;color:rgb(107 114 128);">v{{ $plugin['version'] }}</div>
            @endif
        </div>
        <div style="margin-left:auto;text-align:right;">
            <div style="font-size:1.25rem;font-weight:700;color:rgb(239 68 68);">¥{{ $priceYuan }}</div>
            <div style="font-size:0.75rem;color:rgb(107 114 128);">一次性购买</div>
        </div>
    </div>

    <div style="font-size:0.8125rem;color:rgb(107 114 128);line-height:1.6;margin-bottom:1rem;">
        购买后可永久使用该插件，支持在本系统内一键安装。
    </div>
</div>
