<?php

namespace app\model;

class AdminUser extends BaseModel
{
    protected $hidden = ['id', 'status'];

    /**
     * 获取admin的id
     * @param int $userId 微信用户id
     * @return int|null
     */
    public function getAdminId(int $userId)
    {
        return $this->getField('id', ['wechat_id' => $userId]);
    }


}