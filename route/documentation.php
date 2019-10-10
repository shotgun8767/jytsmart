<?php

// Api文档
use think\facade\Route;

Route::get('documentation', 'documentation/index');

// 获取所有控制器的名称
Route::get('documentation/route/controllers', 'documentation/getControllers');

// 获取某控制器下的全部路由
Route::get('documentation/routes', 'documentation/getRoutes');

// 获取某路由的详细信息
Route::get('documentation/route/detail', 'documentation/getRouteDetail');