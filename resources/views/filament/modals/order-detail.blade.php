<div style="padding: 1rem;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280; width: 120px;">订单号</td>
            <td style="padding: 8px 12px; font-family: monospace;">{{ $order->order_no }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">用户</td>
            <td style="padding: 8px 12px;">{{ $order->user?->name ?? '-' }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">插件名称</td>
            <td style="padding: 8px 12px;">{{ $order->plugin_name }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">插件版本</td>
            <td style="padding: 8px 12px;">{{ $order->plugin_version ?? '-' }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">金额</td>
            <td style="padding: 8px 12px; font-weight: 600;">¥{{ number_format($order->amount / 100, 2) }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">支付方式</td>
            <td style="padding: 8px 12px;">
                @if($order->payment_method === 'alipay') 支付宝
                @elseif($order->payment_method === 'wechat') 微信支付
                @else {{ $order->payment_method ?? '-' }}
                @endif
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">支付流水号</td>
            <td style="padding: 8px 12px; font-family: monospace;">{{ $order->payment_trade_no ?? '-' }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">状态</td>
            <td style="padding: 8px 12px;">{{ \App\Models\PluginOrder::statusLabel($order->status) }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 8px 12px; color: #6b7280;">支付时间</td>
            <td style="padding: 8px 12px;">{{ $order->paid_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; color: #6b7280;">创建时间</td>
            <td style="padding: 8px 12px;">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>
</div>
