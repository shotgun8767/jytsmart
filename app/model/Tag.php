<?php

namespace app\model;

class Tag extends BaseModel
{
    protected $hidden = ['status'];

    /**
     * 获取标签
     * @param int $limit
     * @return array
     */
    public function getInfo(int $limit) : ?array
    {
        return $this
            ->multi($limit)
            ->getArray();
    }
}