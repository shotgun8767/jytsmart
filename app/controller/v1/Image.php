<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\ImageException;
use app\model\Image as model;
use sap\Package;

class Image extends BaseApi
{
    /**
     * 上传一张图片
     * @return Package
     */
    public function upload()
    {
        $res = (new model)->upload('image');

        return $res ?
            Package::created('成功上传图片', ['image_id' => $res]) :
            Package::error(ImageException::class, 130001);
    }

    /**
     * 获取图片信息
     * @param int $imageId
     * @return Package
     * @throws \ReflectionException
     */
    public function get(int $imageId) : Package
    {
        $res = (new model)->getArray($imageId);

        return $res ?
            Package::ok('成功获取图片信息', $res) :
            Package::error(ImageException::class, 130002);
    }
}