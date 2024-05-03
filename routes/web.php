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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

$router->get('/stuff', 'StuffController@index');
$router->post('/stuff', 'StuffController@store');
$router->get('/stuff/trash', 'StuffController@deleted');
$router->delete('/stuff/permanent', 'StuffController@permanentDeleteAll');
$router->delete('/stuff/permanent/{id}', 'StuffController@permanentDelete');
$router->put('/stuff/restore', 'StuffController@restoreAll');
$router->put('/stuff/restore/{id}', 'StuffController@restore');
$router->get('/stuff/{id}', 'StuffController@show');
$router->put('/stuff/{id}', 'StuffController@update');
$router->delete('/stuff/{id}', 'StuffController@destroy');

$router->get('/stuff-stock', 'StuffStockController@index');
$router->post('/stuff-stock', 'StuffStockController@store');
$router->get('/stuff-stock/trash', 'StuffStockController@deleted');
$router->delete('/stuff-stock/permanent', 'StuffStockController@permanentDeleteAlll');
$router->delete('/stuff-stock/permenent/{id}', 'StuffStockController@permanentDelete');
$router->put('/stuff-stock/restore', 'StuffStockController@restoreAll');
$router->put('/stuff-stock/restore/{id}', 'StuffStockController@restore');
$router->get('/stuff-stock/{id}', 'StuffStockController@show');
$router->put('/stuff-stock/{id}', 'StuffStockController@update');
$router->delete('/stuff-stock/{id}', 'StuffStockController@destroy');

$router->get('/user', 'UserController@index');
$router->post('/user', 'UserController@store');
$router->get('/user/trash', 'UserController@deleted');
$router->delete('/user/permanent', 'UserController@permanentDeleteAll');
$router->delete('/user/permanent/{id}', 'UserController@permanentDelete');
$router->put('/user/restore', 'UserController@restoreAll');
$router->put('/user/restore/{id}', 'UserController@restore');
$router->get('/user/{id}', 'UserController@show');
$router->put('/user/{id}', 'UserController@update');
$router->delete('/user/{id}', 'UserController@destroy');

$router->get('/inbound', 'InboundController@index');
$router->post('/inbound', 'InboundController@store');
$router->get('/inbound/trash', 'InboundController@deleted');
$router->delete('/inbound/permanent', 'InboundController@permanentDeleteAll');
$router->delete('/inbound/permanent/{id}', 'InboundController@permanentDelete');
$router->put('/inbound/restore', 'InboundController@restoreAll');
$router->put('/inbound/restore/{id}', 'InboundController@restore');
$router->get('/inbound/{id}', 'InboundController@show');
$router->put('/inbound/{id}', 'InboundController@update');
$router->delete('/inbound/{id}', 'InboundController@destroy');
$router->post('/inbound/file', 'InboundController@upload');

$router->post('/lendings/store', 'LendingController@store');
$router->get('/lendings', 'LendingController@index');
$router->delete('/lendings/delete/{id}', 'LendingController@destroy');
$router->get('/lendings/{id}', 'LendingController@show');

$router->post('/restorations/{lending_id}', 'RestorationController@store');