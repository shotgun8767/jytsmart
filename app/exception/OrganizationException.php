<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class OrganizationException extends BaseException
{
    protected $code = HttpCode::SC_FORBIDDEN;

    protected $message = 'Organization Relative error';

    protected $errcode = 180000;

    protected $errcodes = [
        180001 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '创建主办方失败！'],
        180002 => [HttpCode::SC_NOT_FOUND, '主办方不存在或未更新数据！'],
    ];
}