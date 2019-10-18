<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class UserException extends BaseException
{
    protected $code = HttpCode::SC_FORBIDDEN;

    protected $message = '�û�����쳣';

    protected $errcode = 100000;

    protected $errcodes = [
        100001 => [HttpCode::SC_NOT_FOUND, '�û������ڣ�'],
        100002 => [HttpCode::SC_BAD_REQUEST, '�޸������ݣ�'],
        100003 => [HttpCode::SC_OK, '�û������ڻ�δ�����κ����ݣ�'],
    ];
}