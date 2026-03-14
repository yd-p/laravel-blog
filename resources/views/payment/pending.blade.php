<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>等待支付确认</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 1rem; padding: 3rem 2.5rem; text-align: center; max-width: 420px; width: 90%; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .spinner { width: 4rem; height: 4rem; border: 4px solid #e5e7eb; border-top-color: #4f46e5; border-radius: 9999px; animation: spin 0.8s linear infinite; margin: 0 auto 1.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 1.375rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; }
        p { color: #6b7280; font-size: 0.9375rem; line-height: 1.6; }
        .status { margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280; }
        .btn-link { display: inline-block; margin-top: 2rem; color: #4f46e5; font-size: 0.875rem; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>等待支付确认</h1>
        <p>正在等待支付结果，请稍候...</p>
        <div class="status" id="status-text">正在查询订单状态</div>
        <a href="/admin/plugin-market" class="btn-link">返回插件市场</a>
    </div>

    <script>
        const orderNo = '{{ $orderNo }}';
        let attempts = 0;
        const maxAttempts = 30; // 最多轮询 30 次（约 60 秒）

        function poll() {
            if (attempts >= maxAttempts) {
                document.getElementById('status-text').textContent = '支付确认超时，请前往订单列表查看';
                return;
            }
            attempts++;

            fetch('/payment/query/' + orderNo)
                .then(r => r.json())
                .then(data => {
                    if (data.paid) {
                        window.location.href = '/payment/success?order_no=' + orderNo;
                    } else if (data.status === 3) {
                        document.getElementById('status-text').textContent = '订单已取消';
                    } else {
                        document.getElementById('status-text').textContent = '等待支付中... (' + attempts + ')';
                        setTimeout(poll, 2000);
                    }
                })
                .catch(() => setTimeout(poll, 3000));
        }

        setTimeout(poll, 2000);
    </script>
</body>
</html>
