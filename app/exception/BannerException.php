<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class BannerException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Banner Relative error';

    protected $errcode = 150000;

    protected $errcodes = [
        150001 => [HttpCode::SC_NOT_FOUND, '没有找到轮播图！'],
        150002 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '轮播图上传失败！'],
    ];
}