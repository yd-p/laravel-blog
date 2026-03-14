<div style="text-align:center;padding:1rem 0;">
    <div style="font-size:0.875rem;color:rgb(107 114 128);margin-bottom:1rem;">
        请使用微信扫描下方二维码完成支付
    </div>

    {{-- 二维码展示区 --}}
    <div style="display:inline-flex;align-items:center;justify-content:center;width:200px;height:200px;border:2px solid rgb(229 231 235);border-radius:0.75rem;margin:0 auto;background:#fff;overflow:hidden;">
        @if(!empty($codeUrl))
            {{-- 使用 Google Charts API 生成二维码（生产环境建议换成本地库） --}}
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($codeUrl) }}"
                alt="微信支付二维码"
                style="width:180px;height:180px;"
                onerror="this.parentElement.innerHTML='<div style=\'padding:1rem;font-size:0.75rem;color:rgb(107 114 128);word-break:break-all;\'>二维码加载失败，请复制链接到微信打开</div>'"
            >
        @else
            <div style="font-size:0.8125rem;color:rgb(239 68 68);">二维码获取失败，请重试</div>
        @endif
    </div>

    <div style="margin-top:1rem;font-size:1.125rem;font-weight:700;color:rgb(239 68 68);">
        ¥{{ $amount }}
    </div>

    <div style="margin-top:0.5rem;font-size:0.8125rem;color:rgb(107 114 128);">
        订单号：{{ $orderNo }}
    </div>

    <div style="margin-top:1.25rem;padding:0.75rem;background:rgb(240 253 244);border-radius:0.5rem;font-size:0.8125rem;color:rgb(22 101 52);">
        扫码支付成功后，点击下方「我已完成支付」按钮
    </div>

    {{-- 自动轮询状态 --}}
    <div id="pay-status-tip" style="margin-top:0.75rem;font-size:0.8125rem;color:rgb(107 114 128);"></div>
</div>

<script>
(function() {
    const orderNo = '{{ $orderNo }}';
    let timer = null;
    let count = 0;

    function poll() {
        if (count >= 60) {
            document.getElementById('pay-status-tip').textContent = '支付超时，请手动点击确认';
            return;
        }
        count++;
        fetch('/payment/query/' + orderNo, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.paid) {
                    document.getElementById('pay-status-tip').textContent = '✅ 支付成功！正在处理...';
                    clearTimeout(timer);
                    // 触发 Livewire 方法
                    if (window.Livewire) {
                        Livewire.dispatch('checkWechatPayment');
                    }
                } else {
                    document.getElementById('pay-status-tip').textContent = '等待支付中... (' + count + 's)';
                    timer = setTimeout(poll, 2000);
                }
            })
            .catch(() => { timer = setTimeout(poll, 3000); });
    }

    timer = setTimeout(poll, 3000);
})();
</script>
