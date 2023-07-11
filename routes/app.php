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

use App\Http\Controllers\App\Apps\AppHealthLogController;
use App\Http\Controllers\App\Apps\AppsController;
use Laravel\Lumen\Routing\Router;

/** @var Router $router */


################### 不需要用户认证 #####################
#对外接口
$router->group([
    'prefix' => 'api',
    'middleware' => []
], function () use ($router) {
    $router->get('/',  function () use ($router) {return $router->app->version();});
    $router->post('/', function () use ($router) {return $router->app->version();});

    //APP管理
    $router->group([], function () use ($router) {
        //创建APP
        $router->post('app/create', AppsController::class . '@create');
        //健康上报
        $router->post('app/health/check', AppHealthLogController::class . '@healthCheck');
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

    $router->group(['namespace' => 'Invite'], function () use ($router) {
        $router->get('invite.html', "InviteController@invite");
    });
});
################################需要用户认证################################

