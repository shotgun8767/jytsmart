<?php

namespace app\model;

class Banner extends BaseModel
{
    protected $hidden = ['id', 'status', 'listorder', 'image_id'];

    /**
     * 获取轮播图
     * @param int $limit
     * @return array
     */
    public function getInfo(int $limit) : ?array
    {
        return $this
            ->multi($limit)
            ->baseWith('ImageInfo')
            ->order(['listorder' => 'DESC', 'id' => 'DESC'])
            ->getArray();
    }

    /**
     * 上传一张轮播图
     * @param int $imageId
     * @param int $lectureId
     * @param int $listorder
     * @return int
     */
    public function upload(int $imageId, int $lectureId, int $listorder) : int
    {
        return $this->inserts([
            'image_id' => $imageId,
            'lecture_id' => $lectureId,
            'listorder' => $listorder
        ]);
    }

    public function ImageInfo()
    {
        return $this->belongsTo('Image', 'image_id', 'id');
    }
}