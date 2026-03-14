<x-filament-panels::page>
<form wire:submit="save">

@php
$card = 'background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;';
$label = 'display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:5px;';
$input = 'width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;outline:none;box-sizing:border-box;';
$grid2 = 'display:grid;grid-template-columns:1fr 1fr;gap:16px;';
$grid3 = 'display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;';
$h3 = 'font-size:15px;font-weight:600;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:8px;';
@endphp

{{-- 基本信息 --}}
<div style="{{ $card }}">
    <h3 style="{{ $h3 }}">🌐 基本信息</h3>
    <div style="{{ $grid2 }}">
        <div>
            <label style="{{ $label }}">站点名称</label>
            <input type="text" wire:model="site_name" placeholder="我的博客" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">站点地址</label>
            <input type="url" wire:model="site_url" placeholder="https://example.com" style="{{ $input }}">
        </div>
        <div style="grid-column:span 2;">
            <label style="{{ $label }}">站点简介</label>
            <textarea wire:model="site_description" rows="2" placeholder="一句话描述你的站点" style="{{ $input }}resize:vertical;"></textarea>
        </div>
        <div>
            <label style="{{ $label }}">Logo URL</label>
            <input type="text" wire:model="site_logo" placeholder="/images/logo.png" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">Favicon URL</label>
            <input type="text" wire:model="site_favicon" placeholder="/favicon.ico" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">ICP 备案号</label>
            <input type="text" wire:model="site_icp" placeholder="京ICP备XXXXXXXX号" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">版权信息</label>
            <input type="text" wire:model="site_copyright" placeholder="© 2024 My Blog" style="{{ $input }}">
        </div>
    </div>
</div>

{{-- SEO 搜索优化 --}}
<div style="{{ $card }}">
    <h3 style="{{ $h3 }}">🔍 搜索引擎优化（SEO）</h3>
    <div style="{{ $grid2 }}">
        <div>
            <label style="{{ $label }}">标题后缀 <span style="color:#9ca3af;font-weight:400;">（拼接在页面标题后）</span></label>
            <input type="text" wire:model="seo_title_suffix" placeholder=" - 我的博客" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">Robots 规则</label>
            <select wire:model="seo_robots" style="{{ $input }}">
                <option value="index,follow">index,follow（允许收录）</option>
                <option value="noindex,follow">noindex,follow（不收录）</option>
                <option value="index,nofollow">index,nofollow（不追踪链接）</option>
                <option value="noindex,nofollow">noindex,nofollow（完全屏蔽）</option>
            </select>
        </div>
        <div style="grid-column:span 2;">
            <label style="{{ $label }}">默认 Meta Keywords</label>
            <input type="text" wire:model="seo_keywords" placeholder="博客,技术,Laravel" style="{{ $input }}">
        </div>
        <div style="grid-column:span 2;">
            <label style="{{ $label }}">默认 Meta Description</label>
            <textarea wire:model="seo_description" rows="2" placeholder="站点默认描述，用于搜索引擎摘要" style="{{ $input }}resize:vertical;"></textarea>
        </div>
        <div style="grid-column:span 2;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" wire:model="seo_sitemap_enabled" style="width:15px;height:15px;accent-color:#6366f1;">
                <span style="font-size:14px;color:#374151;">自动生成 Sitemap（/sitemap.xml）</span>
            </label>
        </div>
    </div>

    {{-- 站长验证 --}}
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;">
        <p style="font-size:13px;font-weight:600;color:#6b7280;margin:0 0 12px;">站长工具验证码</p>
        <div style="{{ $grid2 }}">
            <div>
                <label style="{{ $label }}">Google Search Console</label>
                <input type="text" wire:model="google_search_console" placeholder="google-site-verification=xxx" style="{{ $input }}">
            </div>
            <div>
                <label style="{{ $label }}">百度搜索资源平台</label>
                <input type="text" wire:model="baidu_search_console" placeholder="baidu-site-verification=xxx" style="{{ $input }}">
            </div>
        </div>
    </div>

    {{-- 统计代码 --}}
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;">
        <p style="font-size:13px;font-weight:600;color:#6b7280;margin:0 0 12px;">流量统计</p>
        <div style="{{ $grid2 }}">
            <div>
                <label style="{{ $label }}">Google Analytics ID</label>
                <input type="text" wire:model="google_analytics_id" placeholder="G-XXXXXXXXXX" style="{{ $input }}">
            </div>
            <div>
                <label style="{{ $label }}">百度统计 ID</label>
                <input type="text" wire:model="baidu_analytics_id" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" style="{{ $input }}">
            </div>
        </div>
    </div>
</div>

{{-- 社交媒体 --}}
<div style="{{ $card }}">
    <h3 style="{{ $h3 }}">📱 社交媒体</h3>
    <div style="{{ $grid2 }}">
        <div>
            <label style="{{ $label }}">微博主页</label>
            <input type="url" wire:model="social_weibo" placeholder="https://weibo.com/xxx" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">微信公众号</label>
            <input type="text" wire:model="social_wechat_oa" placeholder="公众号名称或二维码链接" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">Twitter / X</label>
            <input type="url" wire:model="social_twitter" placeholder="https://twitter.com/xxx" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">GitHub</label>
            <input type="url" wire:model="social_github" placeholder="https://github.com/xxx" style="{{ $input }}">
        </div>
    </div>
</div>

{{-- 联系方式 --}}
<div style="{{ $card }}">
    <h3 style="{{ $h3 }}">📬 联系方式</h3>
    <div style="{{ $grid3 }}">
        <div>
            <label style="{{ $label }}">联系邮箱</label>
            <input type="email" wire:model="contact_email" placeholder="hello@example.com" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">联系电话</label>
            <input type="text" wire:model="contact_phone" placeholder="400-xxx-xxxx" style="{{ $input }}">
        </div>
        <div>
            <label style="{{ $label }}">地址</label>
            <input type="text" wire:model="contact_address" placeholder="北京市朝阳区..." style="{{ $input }}">
        </div>
    </div>
</div>

{{-- 底部保存 --}}
<div style="display:flex;justify-content:flex-end;padding-bottom:8px;">
    <button type="submit" style="padding:10px 28px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">
        保存设置
    </button>
</div>

</form>
</x-filament-panels::page>
