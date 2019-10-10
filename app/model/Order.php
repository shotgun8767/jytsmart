<?php

namespace app\model;

class Order extends BaseModel
{
    const NOT_PAID = 0;
    const PAID = 1;

    protected $hidden = ['id', 'status'];

    /**
     * 更新订单
     * @param int $number
     * @return int
     * @throws \app\exception\DataBaseException
     */
    public function updateOrder(int $number)
    {
        return $this->updates(['number' => $number], ['pay' => self::PAID]);
    }
}