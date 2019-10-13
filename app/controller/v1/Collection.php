<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\CollectionException;
use app\model\Collection as model;
use sap\Package;

class Collection extends BaseApi
{
    /**
     * 获取收藏
     * @param int $type
     * @param int $page
     * @param int $row
     * @return Package
     * @throws CollectionException
     */
    public function get(int $type, int $page, int $row) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->getByType($userId, $type, $page,$row);

        return $res ?
            Package::ok('成功获取收藏', $res) :
            Package::error(CollectionException::class, 170002);
    }

    /**
     * 添加收藏
     * @param int $type
     * @param int $foreign_id
     * @return Package
     * @throws CollectionException
     */
    public function add(int $type, int $foreign_id) : Package
    {
        $userId = $this->token()->payload('uid');
        $id = (new model)->add($userId, $type, $foreign_id);

        return $id ?
            Package::created('成功添加收藏', ['collection_id' => $id]) :
            Package::error(CollectionException::class, 170003);
    }

    /**
     * 删除收藏
     * @param int $collectionId
     * @return Package
     * @throws CollectionException
     */
    public function delete(int $collectionId) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->deleteOne($userId, $collectionId);

        return $res ?
            Package::ok('成功删除收藏') :
            Package::error(CollectionException::class, 170004);
    }

    public function deleteByForeignId(int $type, int $foreignId) : Package
    {
        $userId = $this->token()->payload('uid');
        $res = (new model)->deleteByForeignId($userId, $type, $foreignId);

        return $res ?
            Package::ok('成功删除收藏') :
            Package::error(CollectionException::class, 170004);
    }
}