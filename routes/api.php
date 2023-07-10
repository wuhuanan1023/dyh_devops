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



$router->group([
    'prefix' => 'api',
], function () use ($router) {

    $router->get('/', function () use ($router) {
        return $router->app->version();
    });
    $router->post('/', function () use ($router) {
        return $router->app->version();
    });


    ################################需要用户认证################################
    $router->group(['middleware' => [
        // 用户认证中间件
        'auth',
    ]], function () use ($router) {

        $router->group(['namespace' => 'Invite'], function () use($router){
            $router->get('/invite.html' ,   "InviteController@invite");
        });

    });

});


