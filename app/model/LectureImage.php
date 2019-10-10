<?php

namespace app\model;

class LectureImage extends BaseModel
{
    protected $hidden = ['status', 'lecture_id', 'image_id', 'listorder'];

    /**
     * 获取会议的关联图片
     * @param int $lectureId
     * @return array
     */
    public function getAll(int $lectureId) : ?array
    {
        return $this
            ->multi()
            ->order(['listorder' => 'DESC'])
            ->baseWith('ImageInfo')
            ->getArray(['lecture_id' => $lectureId]);
    }

    /**
     * 上传一张会议的关联图片
     * @param int $lectureId
     * @param int $listorder
     * @return int
     */
    public function upload(int $lectureId, int $listorder) : int
    {
        return $this->inserts([
            'lecture_id' => $lectureId,
            'listorder' => $listorder
        ]);
    }

    public function ImageInfo()
    {
        return $this->belongsTo('Image', 'image_id', 'id');
    }


}