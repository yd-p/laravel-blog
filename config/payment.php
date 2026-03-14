<?php

return [

    'alipay' => [
        'app_id'                  => env('ALIPAY_APP_ID', ''),
        // RSA2 私钥内容（非文件路径）
        'app_secret_cert'         => env('ALIPAY_PRIVATE_KEY', ''),
        // 证书文件路径（使用证书模式时填写，否则留空）
        'app_public_cert_path'    => env('ALIPAY_APP_CERT_PATH', ''),
        'alipay_public_cert_path' => env('ALIPAY_PUBLIC_CERT_PATH', ''),
        'alipay_root_cert_path'   => env('ALIPAY_ROOT_CERT_PATH', ''),
        'sandbox'                 => env('ALIPAY_SANDBOX', false),
    ],

    'wechat' => [
        'mch_id'               => env('WECHAT_MCH_ID', ''),
        'mch_secret_key'       => env('WECHAT_MCH_SECRET_KEY', ''),
        // 商户私钥文件路径（apiclient_key.pem）
        'mch_secret_cert'      => env('WECHAT_MCH_SECRET_CERT', ''),
        // 商户公钥证书路径（apiclient_cert.pem）
        'mch_public_cert_path' => env('WECHAT_MCH_PUBLIC_CERT_PATH', ''),
        'sandbox'              => env('WECHAT_SANDBOX', false),
    ],

];
