<?php

namespace app\model;

use app\exception\AttendanceException;
use app\service\Lock;
use app\service\WxPay;
use think\db\BaseQuery;
use app\model\status\Attendance as status;

class Attendance extends BaseModel
{
    const CHECK_IN = 1;
    const NOT_CHECK_IN = 0;

    protected $hidden = ['status', 'user_id'];

    /**
     * 获取指定会议的嘉宾
     * @param $userId
     * @param int $lectureId
     * @param int $page
     * @param int $row
     * @return array|null
     */
    public function getAllOfLecture(int $userId, int $lectureId, int $page, int $row) : ?array
    {
        $fields = ['id', 'user_id', 'check_in'];

        $with = ['UserInfo' => function ($query) {
            /** @var BaseQuery $query */
            $query->field(['id', 'name', 'avatar_url', 'occupation']);
        }];

        $res = $this
            ->multi()
            ->page($page, $row)
            ->baseWith($with)
            ->get(['lecture_id' => $lectureId], $fields);

        $Collection = new Collection;
        $res->map(function ($value) use ($userId, $Collection){
            $foreignId = $value['user_id'];
            $value['is_subscribed'] = $Collection->refreshQuery()->recordExists($userId, $Collection::TYPE_USER, $foreignId);
        });

        return $res->toArray();
    }

    /**
     * 查询用户是否已报名
     * @param int $userId
     * @param int $lectureId
     * @return array
     */
    public function checkEnter(int $userId, int $lectureId) : ?array
    {
        $where = [
            'user_id' => $userId,
            'lecture_id' => $lectureId
        ];

        $status = $this
            ->statusAll()
            ->getField('status', $where);

        if (is_null($status) || !in_array($status, [status::NORMAL, status::NOT_PAID])) {
            return ['enter' => false];
        } else {
            return [
                'enter' => true,
                'paid' => $status === status::NOT_PAID ? false : true
            ];
        }
    }

    /**
     * 用户报名参加会议
     * @param int $userId
     * @param int $lectureId
     * @param array $data
     * @return int|array
     */
    public function enter(int $userId, int $lectureId, array $data)
    {
        # 查询是否已报名/保留名额
        $res = $this->checkEnter($userId, $lectureId);
        if ($res['enter']) {
            throw new AttendanceException($res['paid'] ? 140002 : 140003);
        }

        # 校验时间是否在报名时间内
        $Lecture    = new Lecture;
        $info       = $Lecture->getArray($lectureId, ['enter_start', 'enter_end', 'require_fields', 'enter_fee']);
        $enterStart = $info['enter_start'];
        $enterEnd   = $info['enter_end'];
        $enterFee   = $info['enter_fee'];
        $isCharge   = $enterFee == 0 ? false : true;

        if ($enterStart != 0 && $enterStart > time()) {
            throw new AttendanceException(140004);
        }

        if ($enterEnd != 0 && $enterEnd < time()) {
            throw new AttendanceException(140005);
        }

        # 校验必填字段
        $requireFields = array_flip(array_map('trim', explode(',', $info['require_fields'])));
        if (array_intersect_key($requireFields, $data) != $requireFields) {
            throw new AttendanceException(140006);
        }

        # 准备写入一条记录
        $Lock = new Lock;
        $res    = null;
        $times  = 0;     // 并发次数
        $id     = 0;

        # 清除保留名额
        $this->clearReservation($lectureId);

        while ($times < 100) {
            if (!$Lock->isFlock()) {
                $Lock->flock();
                $capacity = $Lecture
                    ->refreshQuery()
                    ->getField('capacity', $lectureId);
                if ($capacity != 0) {
                    $count = $this
                        ->refreshQuery()
                        ->statusAppend('NOT_PAID')
                        ->getCount(['lecture_id' => $lectureId]);
                    if ($count >= $capacity) {
                        throw new AttendanceException(140007);
                    }
                }

                # 报名
                $data['create_time']    = time();
                $data['user_id']        = $userId;
                $data['lecture_id']     = $lectureId;

                if (!$isCharge) {
                    return $this
                        ->refreshQuery()
                        ->inserts($data, false, $isCharge ? 'NOT_PAID' : 'NORMAL');
                }

                $this->getQuery()->startTrans();

                try {
                    # 调用支付接口
                    $WxPay = new WxPay;
                    $enterConfig = config('setting.enter');
                    $body = $enterConfig['order_body'];
                    $detail = $enterConfig['order_detail'];
                    $attach = $enterConfig['order_attach'];

                    $order = $WxPay->generateOrder($userId, $WxPay::ACTION_ENTER, $enterFee, $body, $detail, $attach);
                    $prepayId = $WxPay->getPrepayId($order);
                    $_data = [
                        'number' => $order['out_trade_no'],
                        'pay' => 0,
                        'fee' => $enterFee
                    ];
                    (new Order)->inserts($_data);
                    $this->refreshQuery()->updates($id, ['order' => $order['out_trade_order']]);

                    $res = $WxPay->reSign($prepayId);
                    return $res;
                } catch (\Exception $e) {
                    $this->getQuery()->rollback();
                    throw $e;
                }
            } else {
                // 停滞100毫秒
                usleep(1000);
                $times++;
            }
        }

        # 超时响应 10seconds
        if ($times >= 1000) {
            throw new AttendanceException(140008);
        }

        # 报名失败
        throw new AttendanceException(140009);
    }

    /**
     * 用户签到
     * @param int $userId
     * @param int $lectureId
     * @return int
     * @throws AttendanceException
     */
    public function checkIn(int $userId, int $lectureId) : int
    {
        $where = [
            'user_id'       => $userId,
            'lecture_id'    => $lectureId
        ];
        $res = $this->getArray($where, ['id', 'check_in']);

        if (is_null($res)) {
            throw new AttendanceException(140010);
        }

        if ($res['check_in'] == self::NOT_CHECK_IN) {
            $end = (new Lecture)->getField('end', $lectureId);
            if (is_null($end)) {
                throw new AttendanceException(140012);
            }

            # 会议结束后不得签到
            if (time() > $end) {
                throw new AttendanceException(140013);
            }

            # 更新
            return $this->updates($res['id'], [
                'check_in' => self::CHECK_IN,
                'check_in_time' => time()
            ]);
        } else {
            throw new AttendanceException(140011);
        }
    }

    /**
     * 获取用户报名的会议
     * @param int $userId
     * @param int $page
     * @param int $row
     * @param null|string $month
     * @return array|null
     */
    public function getPersonalLectures(int $userId, int $page, int $row, ?string $month = null) : ?array
    {
        $with = [
            'LectureInfo' => function ($query) {
                /** @var BaseQuery $query */
                $query->field(['id', 'title', 'address', 'start', 'main_image_id']);
                $query->with(['MainImageInfo']);
            }
        ];

        $this->baseJoin('a', [
            'lecture' => [
                'alias' => 'l',
                'condition' => 'a.lecture_id=l.id',
            ]
        ]);

        $where = [
            ['a.user_id', '=', $userId],
            ['l.status', '=', 1],
        ];

        if ($month) {
            $month      = explode('-', $month);
            $month[1]   = (int) $month[1];
            array_push($month, 1);
            $end        = $month; $end[1]++;

            $where = array_merge($where, [
                ['l.start', '>=', strtotime(implode('-', $month))],
                ['l.end', '<', strtotime(implode('-', $end))]
            ]);

        }

        $return = $this
            ->multi()
            ->baseWith($with)
            ->page($page, $row)
            ->order(['listorder' => 'DESC', 'id' => 'DESC'])
            ->getArray($where, ['a.id', 'a.lecture_id']);

        return array_map(function ($array) {
            if (!$lectureId = $array['lecture_info']['id']) {
                return $array;
            }

            return array_merge($array, $this->getCountOfAttendances($lectureId));
        }, $return);
    }

    /**
     * 清除保留名额
     * @param int $lectureId
     */
    protected function clearReservation(int $lectureId) : void
    {
        $reserveTime = (int)config('setting.enter.reserve_time');

        $this
            ->status('NOT_PAID')
            ->softDelete([
                ['lecture_id', '=', $lectureId],
                ['create_time' , '<', time() - $reserveTime],
            ]);
    }

    /**
     * 获取在指定会议中的报名信息
     * @param int $userId
     * @param int $lectureId
     * @return array
     */
    public function getInfo(int $userId, int $lectureId) : ?array
    {
        return $this->getArray([
            'user_id' => $userId,
            'lecture_id' => $lectureId
        ]);
    }

    /**
     * 取消报名保留名额
     * @param int $userId
     * @param int $lectureId
     * @return int
     */
    public function cancel(int $userId, int $lectureId) : int
    {
        $where = ['user_id' => $userId, 'lecture_id' => $lectureId];

        $res = $this
            ->statusAppend(status::NOT_PAID)
            ->getArray($where, ['id', 'status']);

        if (is_null($res)) {
            throw new AttendanceException(140016);
        }

        if ($res['status'] == status::NORMAL) {
            throw new AttendanceException(140017);
        } else {
            # 未付款
            return $this->updateStatus($res['id'], status::DELETED);
        }
    }

    /**
     * 获取会议的报名人数和签到人数
     * @param int $lectureId
     * @return array
     */
    public function getCountOfAttendances(int $lectureId) : array
    {
        if ((new Lecture)->get($lectureId, ['id']) === null) {
            throw new AttendanceException(140012);
        }

        return [
            'enter_count' => $this
                ->refreshQuery()
                ->getCount(['lecture_id' => $lectureId]),
            'check_in_count' => $this
                ->refreshQuery()
                ->getCount(['lecture_id' => $lectureId, 'check_in' => self::CHECK_IN]),
        ];
    }

    /**
     * 用户是否参加会议
     * @param int $userId
     * @param int $lectureId
     * @return bool
     */
    public function isEnter(int $userId, int $lectureId) : bool
    {
        return $this->get(['user_id' => $userId, 'lecture_id' => $lectureId]) ? true : false;
    }


    /**
     * 更新支付状态
     * @param int $order
     * @param bool $success
     * @return int
     */
    public function updatePaid(int $order, bool $success = true)
    {
        return $this->updateStatus(['order' => $order], $success ? status::NORMAL : status::DELETED);
    }

    public function UserInfo()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function LectureInfo()
    {
        return $this->belongsTo('Lecture', 'lecture_id', 'id');
    }
}