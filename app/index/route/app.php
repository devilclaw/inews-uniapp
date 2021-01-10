<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// Route::get('think', function () {
//     return 'hello,ThinkPHP6!';
// });

// Route::get('hello/:name', 'index/hello');


// 注册路由到Detail控制器的index操作
Route::rule('/list/:id', '/Detail/index');

Route::get('/', 'index/index');
Route::get('/index', 'index/index');
Route::get('detail/index', 'detail/index');
Route::post('detail/del', 'detail/del');
Route::post('detail/add', 'detail/add');
Route::get('login/index', 'login/index');
Route::get('login/verify', 'login/verify');
Route::post('login/index', 'login/index');
Route::post('login/checkcapcha', 'login/checkcapcha');

Route::post('index/logout', 'index/logout');


Route::post('reg/checkemail', 'reg/checkemail');
Route::get('reg/index', 'reg/index');
Route::post('reg/index', 'reg/index');
Route::post('reg/checkuname', 'reg/checkuname');

// api调用开启跨域请求
Route::get('uniapi/cates', 'uniapi/cates')->allowCrossDomain();
Route::get('uniapi/list', 'uniapi/list')->allowCrossDomain();
Route::get('uniapi/news', 'uniapi/news')->allowCrossDomain();
Route::get('uniapi/detail', 'uniapi/detail')->allowCrossDomain();
Route::post('uniapi/detail', 'uniapi/detail')->allowCrossDomain();
Route::get('uniapi/login', 'uniapi/login')->allowCrossDomain();
Route::post('uniapi/login', 'uniapi/login')->allowCrossDomain();

Route::get('uniapi/comm', 'uniapi/comm')->allowCrossDomain();
Route::post('uniapi/comm', 'uniapi/comm')->allowCrossDomain();
