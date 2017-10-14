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

/** @var \FastRoute\RouteCollector $router */
$router->post('/upload', ['as' => 'upload', 'uses' => 'UploadController@upload']);
$router->get('/images/{query:.+}', ['as' => 'get', 'uses' => 'GetController@get']);
