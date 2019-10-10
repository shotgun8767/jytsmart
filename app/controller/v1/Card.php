<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\CardException;
use app\model\{Card as model, Image};
use sap\Package;

class Card extends BaseApi
{
    /**
     * 获取用户名片
     * @param int $page
     * @param int $row
     * @return Package
     * @throws \ReflectionException
     * @throws \app\exception\TokenException
     */
    public function get(int $page, int $row) : Package
    {
        $userId = $this->token()->payload('uid');

        $res = (new model)->getInfo($userId, $page, $row);

        return $res ?
            Package::ok('成功获取用户名片', $res) :
            Package::error(CardException::class, 160001);
    }

    /**
     * 添加名片
     * @return Package
     */
    public function upload() : Package
    {
        $imageId = (new Image)->upload('card');

        if ($imageId) {
            $userId = $this->token()->payload('uid');
            (new model)->add($userId, $imageId);
        }

        return $imageId ?
            Package::ok('成功添加名片') :
            Package::error(CardException::class, 160002);
    }

    /**
     * 删除名片
     * @param int $cardId
     * @return Package
     * @throws \ReflectionException
     * @throws \app\exception\TokenException
     */
    public function delete(int $cardId) : Package
    {
        $userId = $this->token()->payload('uid');

        $res = (new model)->deleteOne($userId, $cardId);

        return $res ?
            Package::ok('成功删除名片') :
            Package::error(CardException::class, 160003);
    }
}