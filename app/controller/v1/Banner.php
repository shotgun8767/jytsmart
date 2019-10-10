<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\BannerException;
use app\model\{Banner as model, Image};
use sap\Package;

class Banner extends BaseApi
{
    /**
     * 获取轮播图
     * @param int $limit
     * @return Package
     * @throws \ReflectionException
     */
    public function get(int $limit) : Package
    {
        $res = (new model)->getInfo($limit);

        return $res ?
            Package::ok('成功获取轮播图', $res) :
            Package::error(BannerException::class, 150001);
    }

    /**
     * @param int $lecture_id
     * @param int $listorder
     * @return Package
     */
    public function upload(int $lecture_id, int $listorder) : Package
    {
        $res = null;
        $imageId = (new Image)->upload('image');

        if ($imageId) {
            $res = (new model)->upload($imageId, $lecture_id, $listorder);
        }

        return $res ?
            Package::created('成功创建轮播图', $res) :
            Package::error(BannerException::class, 150002);
    }
}