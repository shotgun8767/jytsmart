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
        if (0 == $limit) $limit = true;

        return $this
            ->multi($limit)
            ->getArray();
    }
}