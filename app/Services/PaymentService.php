<?php

namespace App\Services;

use App\Models\PluginOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Exception\Exception;

class PaymentService
{
    /**
     * 创建支付宝 PC 网页支付，返回跳转表单 HTML
     */
    public function alipayWeb(PluginOrder $order): string
    {
        $this->bootAlipay();

        $result = Pay::alipay()->web([
            'out_trade_no' => $order->order_no,
            'total_amount' => number_format($order->amount / 100, 2, '.', ''),
            'subject'      => '购买插件：' . $order->plugin_name,
            'return_url'   => route('payment.alipay.return'),
            'notify_url'   => route('payment.alipay.notify'),
        ]);

        // 返回自动提交表单 HTML
        return $result->getContent();
    }

    /**
     * 创建微信 Native 扫码支付，返回二维码 URL
     */
    public function wechatNative(PluginOrder $order): string
    {
        $this->bootWechat();

        $result = Pay::wechat()->scan([
            'out_trade_no' => $order->order_no,
            'total_fee'    => $order->amount,
            'body'         => '购买插件：' . $order->plugin_name,
            'notify_url'   => route('payment.wechat.notify'),
        ]);

        return $result->code_url ?? '';
    }

    /**
     * 处理支付宝异步通知
     */
    public function handleAlipayNotify(Request $request): bool
    {
        try {
            $this->bootAlipay();
            $data = Pay::alipay()->callback($request->all());

            if (($data['trade_status'] ?? '') !== 'TRADE_SUCCESS') {
                return false;
            }

            return $this->markOrderPaid(
                $data['out_trade_no'],
                $data['trade_no'],
                'alipay',
                $data->toArray()
            );
        } catch (\Throwable $e) {
            Log::error('支付宝回调处理失败: ' . $e->getMessage(), $request->all());
            return false;
        }
    }

    /**
     * 处理微信异步通知
     */
    public function handleWechatNotify(Request $request): bool
    {
        try {
            $this->bootWechat();
            $data = Pay::wechat()->callback();

            if (($data['result_code'] ?? '') !== 'SUCCESS') {
                return false;
            }

            return $this->markOrderPaid(
                $data['out_trade_no'],
                $data['transaction_id'],
                'wechat',
                $data->toArray()
            );
        } catch (\Throwable $e) {
            Log::error('微信回调处理失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 将订单标记为已支付
     */
    protected function markOrderPaid(string $orderNo, string $tradeNo, string $method, array $raw): bool
    {
        $order = PluginOrder::where('order_no', $orderNo)->first();

        if (!$order || $order->isPaid()) {
            return true; // 幂等处理
        }

        $order->update([
            'status'           => PluginOrder::STATUS_PAID,
            'payment_method'   => $method,
            'payment_trade_no' => $tradeNo,
            'paid_at'          => now(),
            'payment_raw'      => $raw,
        ]);

        Log::info("插件订单支付成功: {$orderNo}, 方式: {$method}");
        return true;
    }

    /**
     * 查询订单支付状态（轮询用）
     */
    public function queryOrderStatus(string $orderNo): int
    {
        $order = PluginOrder::where('order_no', $orderNo)->first();
        return $order ? $order->status : PluginOrder::STATUS_CANCELLED;
    }

    // -------------------------------------------------------------------------
    // 初始化 SDK 配置
    // -------------------------------------------------------------------------

    protected function bootAlipay(): void
    {
        Pay::config([
            'alipay' => [
                'default' => [
                    'app_id'         => config('payment.alipay.app_id'),
                    'app_secret_cert' => config('payment.alipay.app_secret_cert'),
                    'app_public_cert_path' => config('payment.alipay.app_public_cert_path'),
                    'alipay_public_cert_path' => config('payment.alipay.alipay_public_cert_path'),
                    'alipay_root_cert_path'   => config('payment.alipay.alipay_root_cert_path'),
                    'return_url'     => route('payment.alipay.return'),
                    'notify_url'     => route('payment.alipay.notify'),
                    'mode'           => config('payment.alipay.sandbox') ? 'sandbox' : 'normal',
                ],
            ],
            'logger' => [
                'enable' => config('app.debug'),
                'file'   => storage_path('logs/pay.log'),
                'level'  => 'debug',
            ],
            'http' => ['timeout' => 30],
        ]);
    }

    protected function bootWechat(): void
    {
        Pay::config([
            'wechat' => [
                'default' => [
                    'mch_id'          => config('payment.wechat.mch_id'),
                    'mch_secret_key'  => config('payment.wechat.mch_secret_key'),
                    'mch_secret_cert' => config('payment.wechat.mch_secret_cert'),
                    'mch_public_cert_path' => config('payment.wechat.mch_public_cert_path'),
                    'notify_url'      => route('payment.wechat.notify'),
                    'mode'            => config('payment.wechat.sandbox') ? 'sandbox' : 'normal',
                ],
            ],
            'logger' => [
                'enable' => config('app.debug'),
                'file'   => storage_path('logs/pay.log'),
                'level'  => 'debug',
            ],
            'http' => ['timeout' => 30],
        ]);
    }
}
