<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class UserException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'User Relative error';

    protected $errcode = 100000;

    protected $errcodes = [
        100001 => [HttpCode::SC_NOT_FOUND, '用户不存在！'],
        100002 => [HttpCode::SC_BAD_REQUEST, '无更新内容！'],
        100003 => [HttpCode::SC_OK, '用户不存在或未更新任何内容！']
    ];
}