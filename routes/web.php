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

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->get('/poll', function () {
    return view('poll');
});

// $router->get('/find', 'QueueController@findInItemNew');
$router->get('/find', 'QueueController@findInItem');
$router->get('/mod', 'QueueController@reindexWithModifiedMetadata');
$router->get('/run_re_indexer', 'QueueController@runReIndexer');
$router->group(['prefix' => 'update'], function () use ($router) {
    $router->get('/count', 'AssetUpdateController@getCount');
    $router->get('/build', 'AssetUpdateController@buildQueue');
});

$router->group(['prefix' => 'queue'], function () use ($router) {
    $router->get('/view','AssetUpdateController@viewQueue');
    $router->get('/delete','AssetUpdateController@deleteQueue');
    $router->get('/run','AssetUpdateController@runQueue');
});
