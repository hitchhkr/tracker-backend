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

$router->group(['prefix' => 'media'], function () use ($router){

    $router->get('question/{id}.{ext}',['uses' => 'MediaController@fetchQuestion']);

});

$router->group(['prefix' => 'api'], function () use ($router){

    $router->get('info', ['uses' => 'InfoController@getInfo']);

    $router->post('auth', ['uses' => 'AuthController@auth']);

    $router->post('signup',['uses' => 'UsersController@signup']);

//  User Routes
    $router->get('user[/{id}]', ['uses' => 'UsersController@fetch']);
    $router->post('user', ['uses' => 'UsersController@create']);
    $router->delete('user/{id}', ['uses' => 'UsersController@remove']);
//  Audit Routes
    $router->post('audit', ['uses' => 'AuditController@audit']);
//  Quiz Routes
    $router->get('quiz[/{id}]',['uses' => 'QuizController@fetch']);
    $router->post('quiz',['uses' => 'QuizController@create']);
    $router->put('quiz/{id}',['uses' => 'QuizController@update']);
//  Question Routes
    $router->get('question[/{id}]',['uses' => 'QuestionController@fetch']);
    $router->post('question/{id}',['uses' => 'QuestionController@create']);
//  Rounds Routes
    $router->get('rounds/{id}[/{label}]',['uses' => 'QuizController@fetchRounds']);
    $router->post('rounds/{id}',['uses' => 'QuizController@createRound']);


});
