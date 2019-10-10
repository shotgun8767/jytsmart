<?php

// 用于装载json文件夹下的全部路由

use json\JsonFile;
use json\JsonFileException;
use think\facade\App;
use app\route\RestfulRegister;

$dir = App::getAppPath() . '../route/json/';
$files = array_diff(scandir($dir), ['.', '..']);

foreach ($files as $file) {
    try {
        $JsonFile = new JsonFile($dir . $file);
        RestfulRegister::instance()->groupLoad($JsonFile->getContent());
    } catch (JsonFileException $e) {
        continue;
    }
}

$GLOBALS['backend']['route_end'] = microtime();