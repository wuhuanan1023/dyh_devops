<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Platform\PlatformController;
use Laravel\Lumen\Routing\Router;

/** @var Router $router */


################### 不需要用户认证 #####################
$router->get('/', function () use ($router) {
    return $router->app->version();
});
#账户模块
$router->group([
    'prefix' => 'a',
    'middleware' => [
        #'locale',
    ]
], function () use ($router) {

    #后台页面
    $router->group([], function () use ($router) {
        //登录页
        $router->get('login.html', AuthController::class . '@loginView');
    });
    #后台接口
    $router->group([], function () use ($router) {
        $router->post('auth/login', AuthController::class . '@login');
    });

});
################### 不需要用户认证 #####################



################### 需要用户认证 ######################
$router->group([
    'prefix' => 'a',
    'middleware' => [
        'auth:admin',
        //'rbac',
        'admin_log',
    ]
], function () use ($router) {

    #账户管理
    $router->group([], function () use ($router) {
        //登出
        $router->post('auth/logout', AuthController::class . '@logout');
    });

    # 平台管理
    $router->group([], function () use ($router) {
        //平台列表
        $router->post('platform/option', PlatformController::class . '@option');
        //平台列表
        $router->post('platform/list', PlatformController::class . '@list');
        //创建平台
        $router->post('platform/add', PlatformController::class . '@add');
        //修改平台
        $router->post('platform/edit', PlatformController::class . '@edit');
        //删除平台
        $router->post('platform/del', PlatformController::class . '@del');
        //设置平台状态
        $router->post('platform/set/status', PlatformController::class . '@setStatus');
    });



    # APP管理
    $router->group([], function () use ($router) {
        //创建APP
        $router->post('app/create', PlatformController::class . '@create');
    });

});
################### 需要用户认证 ######################
