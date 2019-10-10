<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\LectureException;
use app\model\{Lecture as model, Image, LectureImage};
use plugin\QRCode;
use sap\Package;

class Lecture extends BaseApi
{
    /**
     * 获取公共会议
     * @param int $page
     * @param int $row
     * @param null|int $tag_id
     * @return Package
     */
    public function getPublic(int $page, int $row, ?int $tag_id = null) : Package
    {
        $res = (new model)->getPublic($page, $row, $tag_id);

        return $res ?
            Package::ok('成功获取公共会议', $res) :
            Package::error(LectureException::class, 120001);
    }

    /**
     * 获取私人会议
     * @param int $page
     * @param int $row
     * @param int $userId
     * @param null|int $tag_id
     * @return Package
     */
    public function getPrivate(int $page, int $row, int $userId, ?int $tag_id = null) : Package
    {
        $res = (new model)->getPrivate($page, $row, $userId, $tag_id);

        return $res ?
            Package::ok('成功获取私人会议', $res) :
            Package::error(LectureException::class, 120001);
    }

    /**
     * 获取客户端用户私人会议
     * @return Package
     * @throws \app\exception\TokenException
     */
    public function getPersonal() : Package
    {
        $userId = $this->token()->payload('uid');

        return $this
            ->setMethod('Lecture', 'getPrivate')
            ->setParam(['userId' => $userId])
            ->call();
    }

    /**
     * 获取会议信息
     * @param int $lectureId
     * @return Package
     */
    public function getById(int $lectureId) : Package
    {
        $res = (new model)->getById($lectureId);

        return $res ?
            Package::ok("成功获取会议信息", $res) :
            Package::error(LectureException::class, 120002);
    }

    /**
     * 创建公共会议
     * @return Package
     */
    public function uploadPublic() : Package
    {
        $data = $this->param();

        $lectureId = (new model)->uploadPublic($data);

        # 生成签到二维码
        if ($lectureId) {
            $this->createQRCode($lectureId);
        }

        return $lectureId ?
            Package::created("成功创建公共会议", ['lecture_id' => $lectureId]) :
            Package::error(LectureException::class, 120003);
    }

    /**
     * 创建私人会议
     * @return Package
     * @throws \app\exception\TokenException
     */
    public function uploadPrivate() : Package
    {
        $userId = $this->token()->payload('uid');
        $data = $this->param();

        $lectureId = (new model)->uploadPrivate($data, $userId);

        # 生成签到二维码
        if ($lectureId) {
            $this->createQRCode($lectureId);
        }

        return $lectureId ?
            Package::created("成功创建私人会议", ['lecture_id' => $lectureId]) :
            Package::error(LectureException::class, 120004);
    }

    /**
     * 编辑会议
     * @param int $lectureId
     * @return Package
     * @throws \app\exception\TokenException
     */
    public function edit(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');
        $data = $this->param();

        $Model = new model;
        $Image = new Image;
        $Model->checkSponsor($lectureId, $userId);

        if ($id = $Image->upload('main_image')) {
            $data['main_image_id'] = $id;
        }

        if ($id = $Image->upload('group_image')) {
            $data['group_image_id'] = $id;
        }

        $res = count($data) > 1 ? $Model->refreshQuery()->updates($lectureId, $data) : 0;

        return $res ?
            Package::created("成功修改会议信息") :
            Package::error(LectureException::class, 120006);
    }

    /**
     * 删除会议
     * @param int $lectureId
     * @return Package
     * @throws \app\exception\TokenException
     */
    public function delete(int $lectureId) : Package
    {
        $userId = $this->token()->payload('uid');

        $res = (new model)->deleteOne($lectureId, $userId);

        return $res ?
            Package::ok('成功删除会议') :
            Package::error(LectureException::class, 120006);
    }

    /**
     * 获取签到二维码图片
     * @param int $lectureId
     * @return Package
     */
    public function getQRCodeUrl(int $lectureId) : Package
    {
        $url = (new model)->getQRCodeUrl($lectureId);

        return $url?
            Package::ok('成功获取签到二维码图片', ['url' => $url]) :
            Package::error(LectureException::class, 120001);
    }

    /**
     * 获取报名填写字段
     * @param int $lectureId
     * @return mixed
     */
    public function getEnterFields(int $lectureId) : Package
    {
        $res = (new model)->getEnterFields($lectureId);

        if (is_null($res)) return Package::error(LectureException::class, 120001);

        return $res ?
            Package::ok('成功获取报名填写字段', $res) :
            Package::error(LectureException::class, 120007);
    }

    /**
     * 获取会议关联图片
     * @param int $lectureId
     * @return Package
     */
    public function getRelativePictures(int $lectureId) : Package
    {
        $res = (new LectureImage)->getAll($lectureId);

        return $res ?
            Package::ok('成功获取会议的关联图片', ['relative_pictures' => $res]) :
            Package::error(LectureException::class, 120008);
    }

    /**
     * 添加会议关联图片
     * @param int $lectureId
     * @param int $listorder
     * @return Package
     * @throws LectureException
     * @throws \app\exception\TokenException
     */
    public function addRelativePicture(int $lectureId, int $listorder = 0) : Package
    {
        $userId = $this->token()->payload('uid');

        (new model)->checkSponsor($lectureId, $userId);

        $res = (new LectureImage)->upload($lectureId, $listorder);

        return $res ?
            Package::created('成功添加会议关联图片') :
            Package::error(LectureException::class, 120009);
    }

    /**
     * 删除会议关联图片
     * @param int $lectureId
     * @param int $lectureImageId
     * @return Package
     */
    public function deleteRelativePicture(int $lectureId, int $lectureImageId) : Package
    {
        $userId = $this->token()->payload('uid');

        (new model)->checkSponsor($lectureId, $userId);

        $res = (new LectureImage)->softDelete($lectureImageId);

        return $res ?
            Package::ok('成功删除会议关联图片') :
            Package::error(LectureException::class, 120009);
    }

    /**
     * 创建会议二维码图片
     * @param int $lectureId
     * @return int
     */
    protected function createQRCode(int $lectureId)
    {
        $QRCodeResource = new \app\resource\Qrcode();
        $QRCode = QRCode::new(DOMAIN . "v1/attendance/check_in/$lectureId");
        $QRCode->saveTo($QRCodeResource->getDirPath(), $lectureId);
        $id = (new Image)->add($QRCodeResource->getDir() . $QRCode->getBaseName());
        return (new model)->updates($lectureId, ['qrcode_id' => $id]);
    }
}