<?php

namespace app\model;

use app\exception\LectureException;

class Lecture extends BaseModel
{
    const PUBLIC_RANGE = 0;
    const PRIVATE_RANGE = 1;

    /**
     * 废弃字段
     * @var array
     */
    protected $disuse = [ 'sponsor_signature', 'sponsor_wechat', 'sponsor_telephone', 'read_time'];

    protected $hidden = [
        'status', 'listorder', 'recallable', 'lecture_type', 'main_image_id',
        'group_image_id', 'qrcode_id', 'place_id', "sponsor_id", 'withdraw'
    ];

    /**
     * 报名必填字段
     * @var array
     */
    protected $enterFields = [
        'name' => '姓名',
        'telephone' => '手机',
        'address' => '地址',
        'email' => '邮箱',
        'qq' => 'QQ',
        'wechat' => '微信',
        'school' => '学校',
        'grade' => '年级',
        'class' => '班级',
        'student_number' => '学号',
        'age' => '年龄',
        'gender' => '性别',
        'company' => '工作单位',
        'apartment' => '工作部门',
        'occupation' => '职业',
        'job_number' => '工号',
        'remark' => '备注'
    ];

    /**
     * 根据id获取会议信息
     * @param int $lectureId
     * @return array|null
     */
    public function getById(int $lectureId) : ?array
    {
        return $this
            ->baseWith(['groupImageInfo', 'mainImageInfo', 'qrCodeInfo'])
            ->getArray($lectureId);
    }

    /**
     * 获取公共会议
     * @param int $page
     * @param int $row
     * @param int|null $tagId
     * @param null|string $month
     * @return array
     */
    public function getPublic(int $page, int $row, ?int $tagId = null, ?string $month = null) : ?array
    {
        return $this->getLectures($page, $row, self::PUBLIC_RANGE, $tagId, $month);
    }

    /**
     * 获取私人会议
     * @param int $page
     * @param int $row
     * @param int $userId
     * @param int|null $tagId
     * @param null|string $month
     * @return array|null
     */
    public function getPrivate(int $page, int $row, int $userId, ?int $tagId = null, ?string $month = null) : ?array
    {
        $return = $this->getLectures($page, $row, self::PRIVATE_RANGE, $tagId, $userId, $month);

        $Attendance = new Attendance;
        return array_map(function ($array) use ($Attendance) {
            if (!$lectureId = $array['id']) {
                return $array;
            }

            return array_merge($array, $Attendance->getCountOfAttendances($lectureId));
        }, $return);
    }

    /**
     * 上传公共会议
     * @param array $data
     * @return int
     */
    public function uploadPublic(array $data) : int
    {
        return $this->upload(self::PUBLIC_RANGE, $data);
    }

    /**
     * 上传私人会议
     * @param array $data
     * @param int $userId
     * @return int
     */
    public function uploadPrivate(array $data, int $userId) : int
    {
        return $this->upload(self::PRIVATE_RANGE, $data, $userId);
    }

    /**
     * 删除会议
     * @param int $lectureId
     * @param int $userId
     * @return null|int
     */
    public function deleteOne(int $lectureId, int $userId) : ?int
    {
        $this->checkSponsor($lectureId, $userId);

        return $this->refreshQuery()->softDelete($lectureId);
    }

    /**
     * 检测会议是否由指定用户发起
     * @param int $lectureId
     * @param int $userId
     * @throws LectureException
     */
    public function checkSponsor(int $lectureId, int $userId) : void
    {
        $sponsorId = $this
            ->baseWith(['sponsorInfo'])
            ->getField('sponsor_id', $lectureId);

        if ($sponsorId === 0) return;
        elseif (is_null($sponsorId)) {
            throw new LectureException(120001);
        }
        elseif ($sponsorId !== $userId) {
            throw new LectureException(120005);
        }
    }

    /**
     * @param int $lectureId
     * @return string|null
     */
    public function getQRCodeUrl(int $lectureId) : ?string
    {
        $info = $this
            ->baseWith(['qrCodeInfo'])
            ->getArray($lectureId, ['qrcode_id']);

        return $info['qr_code_info']['image_url'];
    }

    /**
     * 获取报名填写字段
     * @param int $lectureId
     * @return array
     */
    public function getEnterFields(int $lectureId) : ?array
    {
        $fields = $this->getField('require_fields', $lectureId);

        if (is_null($fields)) return null;

        $fields = array_flip(array_map('trim', explode(',', $fields)));

        $return['require_fields'] = array_intersect_key($this->enterFields, $fields);
        $return['optional_fields'] = array_diff_key($this->enterFields, $return['require_fields']);

        return $return;
    }

    /**
     * 获取会议
     * @param int $page
     * @param int $row
     * @param int $range
     * @param int|null $tagId
     * @param int|null $userId
     * @param null|string $month
     * @return array|null
     */
    public function getLectures(int $page, int $row, int $range, ?int $tagId = null, ?int $userId = null, ?string $month = null) : ?array
    {
        $where = ['range' => $range];

        if ($range === self::PRIVATE_RANGE) {
            $where['sponsor_id'] = $userId;
        }

        if ($month) {
            $end = explode('-', $month);
            $end[1] = ((int) $end[1]) + 1;
            $end = implode('-', $end) . '-1';
            $month = "$month-1";

            $this->getQuery()
                ->whereTime('start', '>=', $month)
                ->whereTime('start', '<', $end);
        }

        $join = [];
        if ($tagId) {
            $join = [
                'lecture_tag' => [
                    'alias' => 'lt',
                    'condition' => 'lt.lecture_id=l.id',
                ]
            ];
            $where['lt.tag_id'] = $tagId;
        }

        $fields = [
            'l.id', 'title', 'holder', 'address', 'enter_fee', 'capacity', 'detail',
            'enter_start', 'enter_end', 'start', 'end', 'lat', 'lng', 'group_image_id',
            'main_image_id', 'qrcode_id'
        ];

        $info = $this
            ->multi()
            ->page($page, $row)
            ->order(['listorder' => 'DESC', 'id' => 'DESC'])
            ->baseJoin('l', $join)
            ->baseWith(['groupImageInfo', 'mainImageInfo', 'qrCodeInfo'])
            ->get($where, $fields);

        if (!$info) return null;

        $hidden = ['require_fields', 'range', 'read_time', 'withdraw'];

        return $info->hidden(array_merge($this->hidden, $hidden))->toArray();
    }

    /**
     * 上传会议
     * @param int $range
     * @param array $data
     * @param int|null $userId
     * @return int
     */
    public function upload(int $range, array $data, ?int $userId = null) : int
    {
        if ($range === self::PRIVATE_RANGE) {
            $adminUser = new AdminUser;
            $sponsorId = $adminUser->getAdminId($userId);
            if (is_null($sponsorId)) {
                $sponsorId = $adminUser->inserts(['wechat_id' => $userId]);
            }

            $data['sponsor_id'] = $sponsorId;
        }

        // 处理 place
        $Place = new Place;
        $place = $data['place'];
        $data['place_id'] = $Place->inserts(['name' => $place], true);
        unset($data['place']);

        // 处理require_fields为空的情况

        // 添加记录
        $data['range'] = $range;
        $lectureId = $this->inserts($data);

        // 处理标签
        if ($tags = $data['tag']??null) {
            $Tag = new Tag;
            $LectureTag = new LectureTag;
            $tags = explode(';', $tags);
            foreach ($tags as $tag) {
                $tagId = is_numeric($tag) ? $tag : $Tag->inserts(['name' => $tag], true);
                $LectureTag->inserts([
                    'tag_id' => $tagId,
                    'lecture_id' => $lectureId
                ]);
            }
        }

        return $lectureId;
    }

    /**
     * 获取提款清单
     * @param int $userId
     * @return array|null
     * @throws \ReflectionException
     */
    public function getWithdrawMoneyList(int $userId) : ?array
    {
        $sponsorId = (new AdminUser)->getAdminId($userId);
        $list = $this
            ->multi()
            ->getColumn('id', ['sponsor_id' => $sponsorId, 'withdraw' => 0]);

        if (empty($list)) return null;

        $Attendance = new Attendance;
        $this->multi(false);
        return array_map(function (int $lectureId) use ($Attendance){
            $info = $this->refreshQuery()->getArray($lectureId, ['enter_fee', 'range']);
            $count = $Attendance->refreshQuery()->getCount(['lecture_id' => $lectureId]);
            return [
                'lecture_id' => $lectureId,
                'enter_fee' => $info['enter_fee'],
                'attendance_count' => $count,
                'total' => $count * $info['enter_fee'],
                'range' => $info['range']
            ];
        }, array_values($list));
    }

    public function place()
    {
        return $this->belongsTo('Place', 'place_id', 'id');
    }

    public function groupImageInfo()
    {
        return $this->belongsTo('Image', 'group_image_id', 'id');
    }

    public function mainImageInfo()
    {
        return $this->belongsTo('Image', 'main_image_id', 'id');
    }

    public function qrCodeInfo()
    {
        return $this->belongsTo('Image', 'qrcode_id', 'id');
    }
    public function sponsorInfo()
    {
        return $this->belongsTo('AdminUser', 'sponsor_id', 'id');
    }
}