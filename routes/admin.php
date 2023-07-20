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

use App\Http\Controllers\Admin\Admin\AdminController;
use App\Http\Controllers\Admin\Apps\AppContactController;
use App\Http\Controllers\Admin\Apps\AppHealthRequestController;
use App\Http\Controllers\Admin\Apps\AppHealthRequestDetailController;
use App\Http\Controllers\Admin\Apps\AppsController;
use App\Http\Controllers\Admin\Apps\AppWarningController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Platform\PlatformController;
use App\Http\Controllers\Admin\System\SysAdminLogController;
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

    #账户
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

    # 管理员管理
    $router->group([], function () use ($router) {
        //管理员选项
        $router->post('admin/option', AdminController::class . '@option');
        //管理员列表
        $router->post('admin/list', AdminController::class . '@list');
        //创建管理员
        $router->post('admin/add', AdminController::class . '@add');
        //修改管理员
        $router->post('admin/edit', AdminController::class . '@edit');
        //删除管理员
        $router->post('admin/del', AdminController::class . '@del');
        //设置管理员状态
        $router->post('admin/set/status', AdminController::class . '@setStatus');
        //设置管理员密码
        $router->post('admin/set/pwd', AdminController::class . '@setPassword');
        //设置管理员密码
        $router->post('admin/set/avatar', AdminController::class . '@setAvatar');

        //管理员操作日志
        $router->post('sys/admin/log', SysAdminLogController::class . '@list');
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
        //APP选项
        $router->post('apps/option', AppsController::class . '@option');
        //APP列表
        $router->post('apps/list', AppsController::class . '@list');
        //创建APP
        $router->post('apps/add', AppsController::class . '@add');
        //修改APP
        $router->post('apps/edit', AppsController::class . '@edit');
        //删除APP台
        $router->post('apps/del', AppsController::class . '@del');
        //设置APP状态
        $router->post('apps/set/status', AppsController::class . '@setStatus');
    });

    # APP 联系人管理
    $router->group([], function () use ($router) {
        //APP联系人选项
        $router->post('apps/contact/option', AppContactController::class . '@option');
        //APP联系人列表
        $router->post('apps/contact/list', AppContactController::class . '@list');
        //创建APP联系人
        $router->post('apps/contact/add', AppContactController::class . '@add');
        //修改APP联系人
        $router->post('apps/contact/edit', AppContactController::class . '@edit');
        //删除APP联系人
        $router->post('apps/contact/del', AppContactController::class . '@del');
        //设置APP联系人状态
        $router->post('apps/contact/set/status', AppContactController::class . '@setStatus');
    });

    # APP 健康监测
    $router->group([], function () use ($router) {
        //APP健康监测 选项
        $router->post('apps/health/request/option', AppHealthRequestController::class . '@option');
        //APP健康监测 列表
        $router->post('apps/health/request/list', AppHealthRequestController::class . '@list');
        //APP健康监测 详情
        $router->post('apps/health/request/detail', AppHealthRequestDetailController::class . '@detail');
    });

    # APP告警
    $router->group([], function () use ($router) {
        //APP健康监测 选项
        $router->post('apps/warning/option', AppWarningController::class . '@option');
        //APP健康监测 列表
        $router->post('apps/warning/list', AppWarningController::class . '@list');
        //APP健康监测 修改状态
        $router->post('apps/warning/set/status', AppWarningController::class . '@setStatus');
    });

    # 系统站点管理
    $router->group([], function () use ($router) {
        //APP选项
        $router->post('sys/domain/option', AppsController::class . '@option');
        //APP列表
        $router->post('sys/domain/list', AppsController::class . '@list');
        //创建APP
        $router->post('sys/domain/add', AppsController::class . '@add');
        //修改APP
        $router->post('sys/domain/edit', AppsController::class . '@edit');
        //删除APP台
        $router->post('sys/domain/del', AppsController::class . '@del');
        //设置APP状态
        $router->post('sys/domain/set/status', AppsController::class . '@setStatus');

    });



});
################### 需要用户认证 ######################
