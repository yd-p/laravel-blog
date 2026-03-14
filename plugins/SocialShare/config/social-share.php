<?php

return [
    'platforms' => [
        'wechat'   => ['label' => '微信',     'enabled' => true],
        'weibo'    => ['label' => '微博',     'enabled' => true],
        'twitter'  => ['label' => 'Twitter',  'enabled' => true],
        'facebook' => ['label' => 'Facebook', 'enabled' => true],
        'copy'     => ['label' => '复制链接', 'enabled' => true],
    ],
    'wechat_appid' => env('WECHAT_APPID', ''),
    'button_style' => 'round',
    'show_labels'  => true,
];
