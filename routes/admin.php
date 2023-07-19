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

use App\Http\Controllers\Admin\Apps\AppsController;
use App\Http\Controllers\Admin\Auth\AuthController;
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

    $router->group([], function () use ($router) {
        $router->post('auth/logout', AuthController::class . '@logout');
    });

    # APP管理
    $router->group([], function () use ($router) {
        //创建APP
        $router->post('app/create', AppsController::class . '@create');
    });

});
################### 需要用户认证 ######################
