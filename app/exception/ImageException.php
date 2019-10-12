<?php

namespace app\exception;

use sep\{BaseException,HttpCode};

class ImageException extends BaseException
{
    public $exception = [
        'httpCode' => HttpCode::SC_INTERNAL_SERVER_ERROR,
        'message' => 'Wechat relative exception！',
        'errcode' => 130000,
    ];

    protected $errcodes = [
        130001 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '上传图片失败！'],
        130002 => [HttpCode::SC_NOT_FOUND, '图片不存在！'],
    ];
}