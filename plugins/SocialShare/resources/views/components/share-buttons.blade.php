@php
    $shareUrl   = $url   ?? url()->current();
    $shareTitle = $title ?? (isset($post) ? $post->title : config('app.name'));
    $platforms  = config('social-share.platforms', []);

    $encodedUrl   = urlencode($shareUrl);
    $encodedTitle = urlencode($shareTitle);

    $links = [
        'weibo'    => "https://service.weibo.com/share/share.php?url={$encodedUrl}&title={$encodedTitle}",
        'twitter'  => "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}",
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}",
    ];

    $icons = [
        'wechat'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M8.5 11.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm5 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2zM12 2C6.477 2 2 6.253 2 11.5c0 2.756 1.22 5.226 3.152 6.942L4 22l4.274-2.13A10.96 10.96 0 0 0 12 21c5.523 0 10-4.253 10-9.5S17.523 2 12 2z"/></svg>',
        'weibo'    => '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M10.098 20c-4.194 0-7.6-2.219-7.6-4.955 0-1.52 1.01-3.26 2.76-4.773C7.1 8.67 9.2 7.9 10.9 8.1c.3.04.5.3.46.6-.04.3-.3.5-.6.46-1.4-.17-3.2.5-4.7 1.8-1.5 1.3-2.36 2.8-2.36 4.09 0 2.18 2.9 4.05 6.4 4.05 3.5 0 6.4-1.87 6.4-4.05 0-.3.24-.54.54-.54s.54.24.54.54C17.58 17.78 14.17 20 10.1 20zm7.4-9.5c-.3 0-.54-.24-.54-.54 0-2.7-2.9-4.9-6.46-4.9-.3 0-.54-.24-.54-.54s.24-.54.54-.54c3.9 0 7.04 2.5 7.04 5.98 0 .3-.24.54-.54.54zm-1.8 1.5c-.16 0-.32-.07-.43-.2-.2-.24-.17-.6.07-.8.5-.42.78-.97.78-1.55 0-1.2-1.3-2.18-2.9-2.18-.3 0-.54-.24-.54-.54s.24-.54.54-.54c2.2 0 3.98 1.46 3.98 3.26 0 .84-.37 1.63-1.04 2.2-.1.1-.24.15-.36.15zm-5.6 1.5c-1.7 0-3.08.97-3.08 2.16 0 1.2 1.38 2.16 3.08 2.16s3.08-.97 3.08-2.16c0-1.2-1.38-2.16-3.08-2.16zm.7 2.5c-.36 0-.65-.3-.65-.65s.3-.65.65-.65.65.3.65.65-.3.65-.65.65zm-1.5-.5c-.2 0-.36-.16-.36-.36s.16-.36.36-.36.36.16.36.36-.16.36-.36.36z"/></svg>',
        'twitter'  => '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'copy'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
    ];

    $colors = [
        'wechat'   => '#07C160',
        'weibo'    => '#E6162D',
        'twitter'  => '#000000',
        'facebook' => '#1877F2',
        'copy'     => '#6B7280',
    ];
@endphp

<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;" data-share-url="{{ $shareUrl }}" data-share-title="{{ $shareTitle }}">
    <span style="font-size:13px;color:#6B7280;white-space:nowrap;">分享到：</span>

    @foreach($platforms as $key => $platform)
        @if($platform['enabled'] ?? true)
            @if($key === 'wechat')
                {{-- 微信：弹出二维码浮层 --}}
                <div style="position:relative;display:inline-block;">
                    <button
                        type="button"
                        onclick="toggleWechatQr(this)"
                        title="微信"
                        style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;border:none;cursor:pointer;background:{{ $colors['wechat'] }};color:#fff;transition:opacity .2s;"
                        onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'"
                    >
                        {!! $icons['wechat'] !!}
                    </button>
                    <div class="wechat-qr-popup" style="display:none;position:absolute;bottom:44px;left:50%;transform:translateX(-50%);background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:12px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:999;text-align:center;width:160px;">
                        <img src="/social-share/wechat-qr?url={{ urlencode($shareUrl) }}" alt="微信二维码" style="width:120px;height:120px;" loading="lazy">
                        <p style="margin:6px 0 0;font-size:12px;color:#6B7280;">微信扫码分享</p>
                        <div style="position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:10px;height:10px;background:#fff;border-right:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;transform:translateX(-50%) rotate(45deg);"></div>
                    </div>
                </div>
            @elseif($key === 'copy')
                {{-- 复制链接 --}}
                <button
                    type="button"
                    onclick="copyShareLink(this, '{{ $shareUrl }}')"
                    title="复制链接"
                    style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;border:none;cursor:pointer;background:{{ $colors['copy'] }};color:#fff;transition:opacity .2s;"
                    onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'"
                >
                    {!! $icons['copy'] !!}
                </button>
            @elseif(isset($links[$key]))
                <a
                    href="{{ $links[$key] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="{{ $platform['label'] }}"
                    onclick="window.open(this.href,'share','width=600,height=450');return false;"
                    style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;text-decoration:none;background:{{ $colors[$key] ?? '#6B7280' }};color:#fff;transition:opacity .2s;"
                    onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'"
                >
                    {!! $icons[$key] !!}
                </a>
            @endif
        @endif
    @endforeach
</div>

<script>
function toggleWechatQr(btn) {
    const popup = btn.nextElementSibling;
    const isVisible = popup.style.display !== 'none';
    // 关闭所有其他弹窗
    document.querySelectorAll('.wechat-qr-popup').forEach(p => p.style.display = 'none');
    popup.style.display = isVisible ? 'none' : 'block';
    if (!isVisible) {
        const close = (e) => { if (!btn.parentElement.contains(e.target)) { popup.style.display = 'none'; document.removeEventListener('click', close); } };
        setTimeout(() => document.addEventListener('click', close), 0);
    }
}

function copyShareLink(btn, url) {
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>';
        btn.style.background = '#10B981';
        setTimeout(() => { btn.innerHTML = orig; btn.style.background = '#6B7280'; }, 2000);
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select(); document.execCommand('copy');
        document.body.removeChild(ta);
    });
}
</script>
