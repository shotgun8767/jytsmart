<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\OrganizationException;
use app\model\Organization as model;
use sap\Package;

/**
 * 主办方
 * Class Organization
 * @package app\controller\v1
 */
class Organization extends BaseApi
{
    /**
     * 创建主办方
     * @return Package
     * @throws \ReflectionException
     */
    public function new()
    {
        $data = $this->param();

        $id = (new model)->inserts($data);

        return $id ?
            Package::created('成功创建主办方', ['id' => $id]) :
            Package::error(OrganizationException::class, 180001);
    }

    /**
     * 修改主办方信息
     * @param int $organizationId
     * @return Package
     * @throws \ReflectionException
     */
    public function edit(int $organizationId)
    {
        $data = $this->param();

        $res = (new model)->updates($organizationId, $data);

        return $res ?
            Package::created('成功修改主办方信息') :
            Package::error(OrganizationException::class, 180002);
    }
}