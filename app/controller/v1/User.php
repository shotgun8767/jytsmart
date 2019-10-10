<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\UserException;
use sap\Package;
use app\model\User as model;

class User extends BaseApi
{
    /**
     * 获取客户端用户信息
     * @return Package
     */
    public function getInfo() : Package
    {
        return $this
            ->setParam(['userId' => $this->token()->payload('uid')])
            ->setMethod('User', 'getInfoById')
            ->call();
    }

    /**
     * 获取用户信息
     * @param int $userId
     * @return Package
     * @throws \ReflectionException
     */
    public function getInfoById(int $userId) : Package
    {
        $res = (new model)->getInfo($userId);

        return $res ?
            Package::ok('成功获取用户信息', $res) :
            Package::error(UserException::class, 100001);
    }

    /**
     * 编辑用户信息
     * @return Package
     * @throws \ReflectionException
     * @throws \app\exception\TokenException
     */
    public function edit() : Package
    {
        $userId = $this->token()->payload('uid');

        if (empty($data = $this->param())) {
            return Package::error(UserException::class, 100002);
        }

        $res = (new model)->edit($userId, $data);

        return $res ?
            Package::ok('成功修改用户信息') :
            Package::error(UserException::class, 100003);
    }
}