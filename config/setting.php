<?php

use think\facade\App;

return [
    // 令牌失效时间（秒）
    'token_expire_time' => 15552000,
    // 报名会议保留名额时间（秒）
    'enter_reserve_time' => 300,
    // 提现延迟
    'withdraw_delay' => 0,
    // 文件锁
    'lock' => [
        'filepath' => App::getRootPath() . 'public/lock'
    ],
    // 报名
    'enter' => [
        'reserve_time'  => 1800,
        'order_body'    => '请支付会议报名费用',
        'order_detail'  => '',
        'order_attach'  => ''
    ]
];