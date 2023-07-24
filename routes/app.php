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

use App\Http\Controllers\App\Apps\AppHealthCheckController;
use App\Http\Controllers\App\Apps\AppsController;
use App\Http\Controllers\App\Common\ServerSyncController;
use Laravel\Lumen\Routing\Router;

/** @var Router $router */


################### 不需要用户认证 #####################
$router->group([
    'prefix' => 'api',
    'middleware' => []
], function () use ($router) {

    //根目录
    $router->get('/',  function () use ($router) {return $router->app->version();});
    $router->post('/', function () use ($router) {return $router->app->version();});

    $router->group([], function () use ($router) {
        //服务器同步
        $router->post('servers/server-sync', ServerSyncController::class . '@serverSync');
    });

    ################### 需要APP验签 #####################
    $router->group([
        'middleware' => [
            'app_check_sign' //APP验签
        ]
    ], function () use ($router) {
        //APP管理
        $router->group([], function () use ($router) {
            //健康上报
            $router->post('apps/health/check', AppHealthCheckController::class . '@healthCheck');
        });
    });



});
################### 不需要用户认证 #####################


################################需要用户认证################################
$router->group([
    'prefix' => 'api',
    'middleware' => [
        'auth:app',
    ]
], function () use ($router) {

});
################################需要用户认证################################

