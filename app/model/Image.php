<?php

namespace app\model;

use think\facade\Filesystem;

class Image extends BaseModel
{
    protected $hidden = ['id', 'status'];

    /**
     * 上传一张图片
     * @param string $name
     * @return int
     */
    public function upload(string $name) : int
    {
        $file = request()->file($name);

        if (!$file) return 0;

        $saveName = Filesystem::disk('image')
            ->putFile('', $file, 'md5');

        return $saveName ? $this->add($saveName) : 0;
    }

    /**
     * 添加url至数据库中
     * @param string $url
     * @return int
     */
    public function add(string $url) : int
    {
        return $this->inserts(['image_url' => $url]);
    }

    public function getImageUrlAttr($url)
    {
        static $pre = null;
        if (is_null($pre)) {
            $pre = (new \app\resource\Image)->getDomain();
        }
        return $pre . $url;
    }
}