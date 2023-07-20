<?php

/** @var \Laravel\Lumen\Routing\Router $router */
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

//WS接口
$router->group([
    'prefix' => 'im',
    'namespace' => 'Im'
], function () use ($router) {

    #上报
    $router->post('server-sync', 'WebsocketController@broadcast');

});
