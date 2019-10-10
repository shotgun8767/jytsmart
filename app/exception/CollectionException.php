<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class CollectionException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Collection Relative error';

    protected $errcode = 170000;

    protected $errcodes = [
        170001 => [HttpCode::SC_UNPROCESSABLE_ENTITY, '错误的收藏类型！'],
        170002 => [HttpCode::SC_NOT_FOUND, '没有更多收藏了！'],
        170003 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '添加错误失败！'],
        170004 => [HttpCode::SC_NOT_FOUND, '指定收藏不存在！'],
        170005 => [HttpCode::SC_NOT_FOUND, '检测到指定收藏的关注着与操作者不匹配！'],
    ];
}