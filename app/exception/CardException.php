<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class CardException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Card Relative error';

    protected $errcode = 160000;

    protected $errcodes = [
        160001 => [HttpCode::SC_NOT_FOUND, '没有更多的名片了！'],
        160002 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '上传名片失败！'],
        160003 => [HttpCode::SC_NOT_FOUND, '名片不存在！'],
        160004 => [HttpCode::SC_NOT_FOUND, '检测到名片拥有者和操作者不匹配！'],
    ];
}