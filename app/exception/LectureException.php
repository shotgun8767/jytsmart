<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class LectureException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Token Relative error';

    protected $errcode = 120000;

    protected $errcodes = [
        120001 => [HttpCode::SC_NOT_FOUND, '已无更多会议记录！'],
        120002 => [HttpCode::SC_NOT_FOUND, '会议不存在！'],
        120003 => [HttpCode::SC_SERVICE_UNAVAILABLE, '创建公共会议失败！'],
        120004 => [HttpCode::SC_SERVICE_UNAVAILABLE, '创建私人会议失败！'],
        120005 => [HttpCode::SC_FORBIDDEN, '检测到操作用户和会议发布者不一致！'],
        120006 => [HttpCode::SC_OK, '缺少参数，没有修改会议信息！'],
        120007 => [HttpCode::SC_NOT_FOUND, '获取报名填写字段失败！'],
        120008 => [HttpCode::SC_NOT_FOUND, '会议不存在或指定会议没有关联图片！'],
        120009 => [HttpCode::SC_NOT_FOUND, '关联图片不存在！']
    ];
}