<?php

namespace App\Http\Controllers;

use App\Models\PluginOrder;
use App\Services\PaymentService;
use App\Services\PluginMarketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PluginMarketService $marketService,
    ) {}

    /**
     * 发起支付宝支付（返回自动提交表单）
     */
    public function alipayPay(string $orderNo): Response
    {
        $order = $this->findPendingOrder($orderNo);
        $html  = $this->paymentService->alipayWeb($order);
        return response($html);
    }

    /**
     * 支付宝同步回跳
     */
    public function alipayReturn(Request $request)
    {
        $orderNo = $request->query('out_trade_no', '');
        $order   = PluginOrder::where('order_no', $orderNo)->first();

        if ($order && $order->isPaid()) {
            return redirect()->route('payment.success', ['order_no' => $orderNo]);
        }

        return redirect()->route('payment.pending', ['order_no' => $orderNo]);
    }

    /**
     * 支付宝异步通知
     */
    public function alipayNotify(Request $request): Response
    {
        $success = $this->paymentService->handleAlipayNotify($request);
        return response($success ? 'success' : 'fail');
    }

    /**
     * 微信 Native 扫码支付 — 获取二维码 URL
     */
    public function wechatQrcode(string $orderNo): JsonResponse
    {
        $order   = $this->findPendingOrder($orderNo);
        $codeUrl = $this->paymentService->wechatNative($order);

        return response()->json(['code_url' => $codeUrl, 'order_no' => $orderNo]);
    }

    /**
     * 微信异步通知
     */
    public function wechatNotify(Request $request): Response
    {
        $success = $this->paymentService->handleWechatNotify($request);
        return response($success ? '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>' : '<xml><return_code><![CDATA[FAIL]]></return_code></xml>');
    }

    /**
     * 轮询订单状态（前端 JS 调用）
     */
    public function queryStatus(string $orderNo): JsonResponse
    {
        $order = PluginOrder::where('order_no', $orderNo)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['status' => -1, 'message' => '订单不存在']);
        }

        $data = [
            'status'       => $order->status,
            'status_label' => PluginOrder::statusLabel($order->status),
            'paid'         => $order->isPaid(),
        ];

        // 支付成功后触发安装
        if ($order->isPaid() && !empty($order->download_url)) {
            $data['download_url'] = $order->download_url;
        }

        return response()->json($data);
    }

    /**
     * 支付成功页
     */
    public function success(Request $request)
    {
        $order = PluginOrder::where('order_no', $request->query('order_no'))
            ->where('user_id', auth()->id())
            ->first();

        return view('payment.success', compact('order'));
    }

    /**
     * 支付等待页（同步回跳但尚未收到异步通知）
     */
    public function pending(Request $request)
    {
        $orderNo = $request->query('order_no', '');
        return view('payment.pending', compact('orderNo'));
    }

    // -------------------------------------------------------------------------

    protected function findPendingOrder(string $orderNo): PluginOrder
    {
        $order = PluginOrder::where('order_no', $orderNo)
            ->where('user_id', auth()->id())
            ->where('status', PluginOrder::STATUS_PENDING)
            ->firstOrFail();

        return $order;
    }
}
