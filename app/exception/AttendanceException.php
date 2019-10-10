<?php

namespace app\exception;

use sep\{BaseException, HttpCode};

class AttendanceException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Attendance Relative error';

    protected $errcode = 140000;

    protected $errcodes = [
        140001 => [HttpCode::SC_NOT_FOUND, '指定会议不存在或会议暂无人参与！'],
        140002 => [HttpCode::SC_FORBIDDEN, '用户已报名会议！'],
        140003 => [HttpCode::SC_FORBIDDEN, '用户名额已被保留，但未付款！'],
        140004 => [HttpCode::SC_FORBIDDEN, '报名未开始！'],
        140005 => [HttpCode::SC_FORBIDDEN, '报名已结束！'],
        140006 => [HttpCode::SC_FORBIDDEN, '缺少必填字段！'],
        140007 => [HttpCode::SC_FORBIDDEN, '报名人数已满！'],
        140008 => [HttpCode::SC_GATEWAY_TIMEOUT, '服务器响应超时！'],
        140009 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '报名失败！'],
        140010 => [HttpCode::SC_FORBIDDEN, '用户未报名会议或未付款！'],
        140011 => [HttpCode::SC_OK, '嘉宾已签到！'],
        140012 => [HttpCode::SC_NOT_FOUND, '会议不存在！'],
        140013 => [HttpCode::SC_FORBIDDEN, '会议已结束，不能签到！'],
        140014 => [HttpCode::SC_FORBIDDEN, '签到失败！'],
        140015 => [HttpCode::SC_NOT_FOUND, '没有更多会议了！'],
        140016 => [HttpCode::SC_NOT_FOUND, '用户未报名会议！'],
        140017 => [HttpCode::SC_NOT_FOUND, '用户已报名会议且付款！'],
        140018 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '未能成功取消报名！'],
        140019 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '报名失败！'],
    ];
}