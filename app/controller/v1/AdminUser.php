<?php

namespace app\controller\v1;

use app\model\{AdminUser as model, Lecture};
use app\api\BaseApi;
use sap\Package;

class AdminUser extends BaseApi
{
    /**
     * 获取提款清单
     * @return Package
     * @throws \ReflectionException
     * @throws \app\exception\TokenException
     */
    public function getWithdrawList() : Package
    {
        $userId = $this->token()->payload('uid');
        $Lecture = new Lecture;
        $list = $Lecture->getWithdrawMoneyList($userId);

        return Package::ok('', $list ? $list : []);
    }
}