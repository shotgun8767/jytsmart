<?php

namespace app\model;

use app\exception\CollectionException;
use think\db\BaseQuery;

class Collection extends BaseModel
{
    /**
     * collection_type
     */
    const TYPE_USER     = 1;
    const TYPE_CARD     = 2;
    const TYPE_LECTURE  = 3;

    protected $hidden = ['status', 'foreign_id', 'user_id', 'collection_type'];

    /**
     * 根据类型获取收藏
     * @param int $userId
     * @param int $type
     * @param int $page
     * @param int $row
     * @return array|null
     */
    public function getByType(int $userId, int $type, int $page, int $row) : ?array
    {
        switch ($type) {
            case self::TYPE_USER    :
                $with = ['UserInfo' => function ($query) {
                    /** @var BaseQuery $query */
                    $query->field(['id', 'name', 'avatar_url', 'occupation']);
                }];
                break;
            case self::TYPE_CARD    :
                $with = ['CardInfo'=> function ($query) {
                    /** @var BaseQuery $query */
                    $query->with(['ImageInfo']);
                }];
                break;
            case self::TYPE_LECTURE :
                $with = ['LectureInfo'=> function ($query) {
                    /** @var BaseQuery $query */
                    $query->with(['groupImageInfo', 'mainImageInfo', 'qrCodeInfo']);
                }];
                break;
            default: throw new CollectionException(170001);
        }

        return $this
            ->multi()
            ->baseWith($with)
            ->order(['id' => 'DESC'])
            ->page($page, $row)
            ->getArray([
                'user_id' => $userId,
                'collection_type' => $type
            ]);
    }

    /**
     * 添加一条收藏记录
     * @param int $userId
     * @param int $type
     * @param int $foreignId
     * @return int
     * @throws CollectionException
     */
    public function add(int $userId, int $type, int $foreignId) : int
    {
        if (!in_array($type, [1, 2, 3])) {
            throw new CollectionException(170001);
        }

        return $this->inserts([
            'user_id'           => $userId,
            'collection_type'   => $type,
            'foreign_id'        => $foreignId
        ], true);
    }

    /**
     * 删除一条收藏记录
     * @param int $userId
     * @param int $collectionId
     * @return int
     * @throws CollectionException
     */
    public function deleteOne(int $userId, int $collectionId) : int
    {
        $uid = $this->getField('user_id', $collectionId);

        if (is_null($uid)) {
            throw new CollectionException(170004);
        }

        if ($userId != $uid) {
            throw new CollectionException(170005);
        }

        return $this->refreshQuery()->softDelete($collectionId);
    }

    /**
     * @param int $userId
     * @param int $type
     * @param int $foreignId
     * @return bool
     */
    public function recordExists(int $userId, int $type, int $foreignId) : bool
    {
        if (!in_array($type, [1, 2, 3])) {
            throw new CollectionException(170001);
        }

        return $this->get([
            'user_id' => $userId,
            'collection_type' => $type,
            'foreign_id' => $foreignId
        ]) ? true : false;
    }

    /**
     * @param int $userId
     * @param int $type
     * @param int $foreignId
     * @return int
     */
    public function deleteByForeignId(int $userId, int $type, int $foreignId) : int
    {
        if (!in_array($type, [1, 2, 3])) {
            throw new CollectionException(170001);
        }

        return $this->softDelete([
            'user_id' => $userId,
            'collection_type' => $type,
            'foreign_id' => $foreignId
        ]);
    }

    public function UserInfo()
    {
        return $this->belongsTo('User', 'foreign_id', 'id');
    }

    public function CardInfo()
    {
        return $this->belongsTo('Card', 'foreign_id', 'id');
    }

    public function LectureInfo()
    {
        return $this->belongsTo('Lecture', 'foreign_id', 'id');
    }
}