<x-filament-panels::page>
    <form wire:submit="save">
        {{-- 平台开关 --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#111827;margin:0 0 16px;">启用平台</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
                @foreach([
                    ['field'=>'wechat_enabled',   'label'=>'微信',     'icon'=>'🟢'],
                    ['field'=>'weibo_enabled',    'label'=>'微博',     'icon'=>'🔴'],
                    ['field'=>'twitter_enabled',  'label'=>'Twitter',  'icon'=>'🐦'],
                    ['field'=>'facebook_enabled', 'label'=>'Facebook', 'icon'=>'🔵'],
                    ['field'=>'copy_enabled',     'label'=>'复制链接', 'icon'=>'📋'],
                ] as $platform)
                    <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;background:{{ $this->{$platform['field']} ? '#f0fdf4' : '#f9fafb' }};">
                        <input type="checkbox"
                               wire:model.live="{{ $platform['field'] }}"
                               style="width:16px;height:16px;accent-color:#16a34a;">
                        <span style="font-size:18px;">{{ $platform['icon'] }}</span>
                        <span style="font-size:14px;font-weight:500;color:#374151;">{{ $platform['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 微信 AppID --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#111827;margin:0 0 16px;">微信配置</h3>
            <div>
                <label style="display:block;font-size:14px;font-weight:500;color:#374151;margin-bottom:6px;">
                    微信 AppID <span style="color:#6b7280;font-weight:400;">（用于 JS-SDK 分享，可选）</span>
                </label>
                <input type="text"
                       wire:model="wechat_appid"
                       placeholder="wx1234567890abcdef"
                       style="width:100%;max-width:400px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;outline:none;">
            </div>
        </div>

        {{-- 按钮样式 --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#111827;margin:0 0 16px;">按钮样式</h3>
            <div style="display:flex;gap:16px;">
                @foreach(['round'=>'圆形按钮', 'square'=>'方形按钮'] as $value => $label)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="radio"
                               wire:model.live="button_style"
                               value="{{ $value }}"
                               style="width:16px;height:16px;accent-color:#6366f1;">
                        <span style="font-size:14px;color:#374151;">{{ $label }}</span>
                        <span style="display:inline-block;width:32px;height:32px;background:#6366f1;
                              border-radius:{{ $value === 'round' ? '50%' : '6px' }};"></span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 显示文字标签 --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#111827;margin:0 0 16px;">显示选项</h3>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox"
                       wire:model.live="show_labels"
                       style="width:16px;height:16px;accent-color:#6366f1;">
                <span style="font-size:14px;color:#374151;">在按钮下方显示文字标签</span>
            </label>
        </div>

        {{-- 预览 --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#111827;margin:0 0 16px;">效果预览</h3>
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                @foreach([
                    ['field'=>'wechat_enabled',   'label'=>'微信',     'color'=>'#07c160', 'icon'=>'💬'],
                    ['field'=>'weibo_enabled',    'label'=>'微博',     'color'=>'#e6162d', 'icon'=>'📢'],
                    ['field'=>'twitter_enabled',  'label'=>'Twitter',  'color'=>'#1da1f2', 'icon'=>'🐦'],
                    ['field'=>'facebook_enabled', 'label'=>'Facebook', 'color'=>'#1877f2', 'icon'=>'👍'],
                    ['field'=>'copy_enabled',     'label'=>'复制链接', 'color'=>'#6b7280', 'icon'=>'📋'],
                ] as $p)
                    @if($this->{$p['field']})
                        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                            <div style="width:44px;height:44px;background:{{ $p['color'] }};
                                 border-radius:{{ $button_style === 'round' ? '50%' : '8px' }};
                                 display:flex;align-items:center;justify-content:center;font-size:20px;">
                                {{ $p['icon'] }}
                            </div>
                            @if($show_labels)
                                <span style="font-size:11px;color:#6b7280;">{{ $p['label'] }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- 保存按钮（底部备用） --}}
        <div style="display:flex;justify-content:flex-end;">
            <button type="submit"
                    style="padding:10px 24px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">
                保存设置
            </button>
        </div>
    </form>
</x-filament-panels::page>
