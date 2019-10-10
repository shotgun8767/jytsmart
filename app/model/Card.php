<?php

namespace app\model;

use app\exception\CardException;

class Card extends BaseModel
{
    protected $hidden = ['id', 'status', 'image_id', 'user_id'];

    /**
     * 获取用户名片
     * @param int $userId
     * @param int $page
     * @param int $row
     * @return array
     */
    public function getInfo(int $userId, int $page, int $row) : ?array
    {
        return $this
            ->multi()
            ->page($page, $row)
            ->baseWith('ImageInfo')
            ->getArray(['user_id' => $userId]);
    }

    /**
     * 添加一张名片
     * @param int $userId
     * @param int $imageId
     * @return int
     */
    public function add(int $userId, int $imageId) : int
    {
        return $this->inserts([
            'user_id' => $userId,
            'image_id' => $imageId
        ], true);
    }

    /**
     * 删除一张名片
     * @param int $userId
     * @param int $cardId
     * @return int
     */
    public function deleteOne(int $userId, int $cardId) : int
    {
        $uid = $this->getField('user_id', $cardId);

        if ($uid != $userId) {
            throw new CardException(160004);
        }

        return $this
            ->refreshQuery()
            ->softDelete($cardId);
    }

    public function ImageInfo()
    {
        return $this->belongsTo('Image', 'image_id', 'id');
    }
}