<?php

namespace Plugins\SocialShare\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ShareController extends Controller
{
    /**
     * 返回当前 URL 的微信二维码图片（SVG data URI）
     * 前端通过 <img src="/social-share/wechat-qr?url=..."> 调用
     */
    public function wechatQr(Request $request): \Illuminate\Http\Response
    {
        $url = $request->query('url', url('/'));

        // 使用 Google Charts API 生成二维码（无需额外依赖）
        $qrUrl = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($url);

        return response()->view('social-share::components.wechat-qr', [
            'url'   => $url,
            'qrUrl' => $qrUrl,
        ]);
    }
}
