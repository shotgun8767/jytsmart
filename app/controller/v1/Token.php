<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\TokenException;
use app\service\permission\User;
use sap\Package;
use jwt\exception\AlgFileException;
use think\facade\Request;

class Token extends BaseApi
{
    /**
     * 获取令牌
     * @param array $payload
     * @param string|null $permission
     * @return Package
     * @throws AlgFileException
     */
    public function get(array $payload, ?string $permission = null) : Package
    {
        $Token = new \app\service\Token(User::class);

        if (is_string($permission)) {

            $Token->setPermission($permission);
        }

        $token = $Token
            ->setPayload($payload)
            ->setExpire(config('setting.token_expire_time'))
            ->getToken();

        return Package::ok('成功获取令牌', ['token' => $token]);
    }

    /**
     * 获取开发者令牌
     * @param int $user_id  用户id
     * @return Package
     */
    public function getDevToken(int $user_id) : Package
    {
        $payload = [
            'uid' => $user_id,
        ];

        return $this
            ->setMethod('Token', 'get')
            ->setParam(['payload' => $payload, 'permission' => 'DEVELOPER'])
            ->call()
            ->message('成功获取开发者令牌');
    }

    /**
     * 获取令牌信息
     * @return Package
     * @throws TokenException
     */
    public function getInfo() : Package
    {
        if (!$token = Request::header('token')) {
            throw new TokenException(50002);
        }

        $Token = new \app\service\Token(User::class, $token);

        $data = [
            'payload' => $Token->getPayload(),
            'expire' => $Token->isExpire(),
            'expire_time' => $Token->getExpire(),
        ];

        return Package::ok('成功获取令牌信息', $data);
    }
}