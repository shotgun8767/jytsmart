<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class WxException extends BaseException
{
    public $exception = [
        'httpCode' => HttpCode::SC_BAD_GATEWAY,
        'message' => 'Wechat relative exception！',
        'errcode' => 110000,
    ];

    protected $errcodes = [
        110001 => [HttpCode::SC_BAD_GATEWAY, 'Fail to get openid from wechat backend!'],
        110002 => [HttpCode::SC_BAD_GATEWAY, 'Fail to get openid from wechat backend! Error result returned!'],
        110003 => [HttpCode::SC_BAD_GATEWAY, 'Token is missing or token given is invalid!'],
        110004 => [HttpCode::SC_BAD_GATEWAY, 'Fail to get access_token from wx backend!'],
        110005 => [HttpCode::SC_BAD_GATEWAY, 'Fail to get wx mini-program qr_code from wx backend!'],
        110006 => [HttpCode::SC_BAD_GATEWAY, 'Fail to get prepay_id from wx backend!'],
    ];
}