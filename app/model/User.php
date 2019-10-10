<?php

namespace app\model;

class User extends BaseModel
{
    protected $hidden = ['status', 'open_id'];

    /**
     * 获取用户信息
     * @param int $userId
     * @return array|null
     */
    public function getInfo(int $userId) : ?array
    {
        return $this
            ->hidden(['status', 'open_id', 'id'])
            ->getArray($userId);
    }

    /**
     * 根据openid获取用户id
     * @param string $openid
     * @return int
     */
    public function getIdByOpenid(string $openid) : int
    {
        if ($id = $this->getField('id', ['open_id' => $openid])) {
            return $id;
        }

        return $this->inserts(['open_id' => $openid], false);
    }

    /**
     * 编辑用户信息
     * @param int $userId
     * @param array $data
     * @return int
     * @throws \app\exception\DataBaseException
     */
    public function edit(int $userId, array $data) : int
    {
        return $this->updates($userId, $data);
    }

    /**
     * 获取openid
     * @param int $userId
     * @return string|null
     */
    public function getOpenId(int $userId) : ?string
    {
        return $this->getField('open_id', $userId);
    }
}