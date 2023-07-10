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

use Laravel\Lumen\Routing\Router;

/** @var Router $router */


################### 不需要用户认证 #####################
$router->get('/', function () use ($router) {
    return $router->app->version();
});

#账户模块
$router->group([
    'prefix' => 'admin',
    'middleware' => [
        #'locale',
    ]
], function () use ($router) {

    #登录
    $router->group([], function () use ($router) {
        //账户登录
        $router->post('auth/login', AuthController::class . '@login');
    });


});
################### 不需要用户认证 #####################



################### 需要用户认证 ######################
$router->group([
    'prefix' => 'admin',
    'middleware' => [
        'auth:admin',
        //'rbac',
        //'admin_log',
    ]
], function () use ($router) {


});
################### 需要用户认证 ######################
