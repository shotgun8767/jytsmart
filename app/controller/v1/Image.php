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
            Package::ok('成功上传图片', ['image_id' => $res]) :
            Package::error(ImageException::class, 130001);
    }
}