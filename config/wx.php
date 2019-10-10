<?php

return [
    // 应用id
    'appid'        => 'wxb3960c8479b59425',
    // 密码
    'secret'    => '2fef801bca148d644e6f81ff89b93f0f',

    // 商户id
    'mch_id'        => '1531349751',
    // 商户平台秘钥
    'mch_key'       => '2fef801bca148d644e6f81ff89b93api',

    // 微信后台提供的API接口
    'api' => [
        // 根据code获取openid和session_key
        'get_openid'   => 'https://api.weixin.qq.com/sns/jscode2session',
        // 获取ACCESS_TOKEN
        'get_access_token' => 'https://api.weixin.qq.com/cgi-bin/token',
        // 获取微信小程序二维码
        'get_wx_qrcode' => 'https://api.weixin.qq.com/wxa/getwxacodeunlimit',
        // 统一下单
        'unified_order' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',

        'pay_notify'    => ''
    ],
];