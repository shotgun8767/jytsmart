<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\AttendanceException;
use app\model\Attendance as model;
use sap\Package;

class Attendance extends BaseApi
{
    /**
     * 获取指定会议的嘉宾
     * @param int $lectureId
     * @param int $page
     * @param int $row
     * @return Package
     */
    public function getAllOfLecture(int $lectureId, int $page, int $row) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->getAllOfLecture($userId, $lectureId, $page, $row);

        return $res ?
            Package::ok('成功获取指定会议的嘉宾', $res) :
            Package::error(AttendanceException::class, 140001);
    }

    /**
     * 查询用户是否已报名
     * @param int $lectureId
     * @return Package
     */
    public function checkEnter(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->checkEnter($userId, $lectureId);

        return Package::ok('成功查询用户是否已报名', $res);
    }

    /**
     * 用户报名参加会议
     * @param int $lectureId
     * @return Package
     * @throws \app\exception\TokenException
     */
    public function enter(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');
        $data = $this->param();

        $res = (new model)->enter($userId, $lectureId, $data);

        if (is_int($res)) {
            return Package::created('用户成功报名参加会议');
        }

        if (is_array($res)) {
            return Package::created("成功预留名额，等待付款", $res);
        }

        return Package::error(AttendanceException::class, 140019);
    }

    /**
     * 用户
     * @param int $lectureId
     * @return Package
     * @throws AttendanceException
     */
    public function checkIn(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->checkIn($userId, $lectureId);

        return $res ?
            Package::ok('成功签到') :
            Package::error(AttendanceException::class, 140014);
    }

    /**
     * 获取用户报名的会议
     * @param int $page
     * @param int $row
     * @param null|string $month
     * @return Package
     */
    public function getPersonalLectures(int $page, int $row, ?string $month = null) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->getPersonalLectures($userId, $page, $row, $month);

        return $res ?
            Package::ok('成功获取用户报名的会议', $res) :
            Package::error(AttendanceException::class, 140015);
    }

    /**
     * 获取在指定会议中的报名信息
     * @param int $lectureId
     * @return Package
     */
    public function getInfo(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->getInfo($userId, $lectureId);

        return $res ?
            Package::ok('成功获取在指定会议中的报名信息', $res) :
            Package::error(AttendanceException::class, 140010);
    }

    /**
     * 取消报名保留名额
     * @param int $lectureId
     * @return Package
     */
    public function cancel(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->cancel($userId, $lectureId);

        return $res ?
            Package::ok('成功取消报名保留名额', $res) :
            Package::error(AttendanceException::class, 140018);
    }

    /**
     * 获取会议的报名人数和签到人数
     * @param int $lectureId
     * @return Package
     */
    public function getCount(int $lectureId) : Package
    {
        $res = (new model)->getCountOfAttendances($lectureId);

        return Package::ok('成功获取会议的报名人数和签到人数', $res);
    }
}