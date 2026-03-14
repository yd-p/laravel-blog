<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付成功</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 1rem; padding: 3rem 2.5rem; text-align: center; max-width: 420px; width: 90%; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .icon { width: 5rem; height: 5rem; background: #dcfce7; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .icon svg { width: 2.5rem; height: 2.5rem; color: #16a34a; }
        h1 { font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; }
        p { color: #6b7280; font-size: 0.9375rem; line-height: 1.6; }
        .info { background: #f9fafb; border-radius: 0.5rem; padding: 1rem; margin: 1.5rem 0; text-align: left; }
        .info-row { display: flex; justify-content: space-between; font-size: 0.875rem; padding: 0.25rem 0; }
        .info-row span:first-child { color: #6b7280; }
        .info-row span:last-child { font-weight: 500; color: #111827; }
        .btn { display: inline-block; margin-top: 1.5rem; padding: 0.75rem 2rem; background: #4f46e5; color: #fff; border-radius: 0.5rem; text-decoration: none; font-weight: 500; font-size: 0.9375rem; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1>支付成功</h1>
        <p>感谢购买，插件已准备就绪</p>

        @if($order)
        <div class="info">
            <div class="info-row"><span>订单号</span><span>{{ $order->order_no }}</span></div>
            <div class="info-row"><span>插件名称</span><span>{{ $order->plugin_name }}</span></div>
            <div class="info-row"><span>支付金额</span><span>¥{{ $order->amount_yuan }}</span></div>
            <div class="info-row"><span>支付方式</span><span>{{ $order->payment_method === 'alipay' ? '支付宝' : '微信支付' }}</span></div>
            <div class="info-row"><span>支付时间</span><span>{{ $order->paid_at?->format('Y-m-d H:i') }}</span></div>
        </div>
        @endif

        <a href="/admin/plugin-market" class="btn">返回插件市场</a>
    </div>
</body>
</html>
