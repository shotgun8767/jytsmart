<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\model\Tag as model;
use sap\Package;

class Tag extends BaseApi
{
    /**
     * 获取标签
     * @param int $limit
     * @return Package
     * @throws \ReflectionException
     */
    public function get(?int $limit = 0) : Package
    {
        $res = (new model)->getInfo($limit);

        return Package::ok('成功获取标签', $res);
    }
}