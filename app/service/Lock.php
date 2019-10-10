<?php

namespace app\service;

class Lock
{
    /**
     * 文件路径
     * @var string
     */
    private $file;

    /**
     * @var resource
     */
    private $fp;

    /**
     * Lock constructor.
     */
    public function __construct()
    {
        $this->file = config('setting.lock.filepath');
    }

    /**
     * 锁死文件
     * @return bool 是否成功锁上文件
     */
    public function flock() : bool
    {
        $this->fp = fopen($this->file, 'w+');
        return flock($this->fp, LOCK_EX);
    }

    /**
     * 释放文件
     */
    public function release() : void
    {
        @flock($this->fp, LOCK_UN);
        @fclose($this->fp);
    }

    protected function __destruct()
    {
        $this->release();
    }
}