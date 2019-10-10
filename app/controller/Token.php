<?php

namespace app\controller\v2;

use app\api\BaseApi;
use app\service\permission\User;
use json\JsonFile;
use sap\Package;
use jwt\exception\AlgFileException;
use think\facade\App;

class Token extends BaseApi
{
    /**
     * 获取开发者令牌
     * @param int $user_id
     * @return Package
     * @throws AlgFileException
     */
    public function getDevToken(int $user_id) : Package
    {
        $Token = new \app\service\Token(User::class);

        $payload = [
            'uid' => $user_id
        ];

        $token = $Token
            ->setPayload($payload)
            ->getToken();

        return Package::ok('成功获取开发者令牌', ['token' => $token]);
    }

    public function test()
    {
        $x = 'success';

        return Package::ok('test method', [$x]);
    }
}