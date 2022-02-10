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
    $router->get('application/{id}.{ext}',['uses' => 'MediaController@fetchApplication']);
    $router->get('11111/{id}_{type}.{ext}',['uses' => 'MediaController@fetchOne']);

});

$router->group(['prefix' => 'api'], function () use ($router){

    $router->get('info', ['uses' => 'InfoController@getInfo']);

    $router->post('auth', ['uses' => 'AuthController@auth']);
    $router->post('reset/{type}', ['uses' => 'UsersController@reset']);

    $router->post('signup',['uses' => 'UsersController@signup']);

//  User Routes
    $router->get('user[/{id}]', ['uses' => 'UsersController@fetch']);
    $router->post('user', ['uses' => 'UsersController@create']);
    $router->post('user/preferences/{type}/{id}', ['uses' => 'UsersController@createPrefs']);
    $router->delete('user/{id}', ['uses' => 'UsersController@remove']);
//  Audit Routes
    $router->post('audit', ['uses' => 'AuditController@audit']);
//  Quiz Routes
    $router->get('quiz[/{id}]',['uses' => 'QuizController@fetch']);
    $router->post('quiz',['uses' => 'QuizController@create']);
    $router->put('quiz/{id}',['uses' => 'QuizController@update']);
    $router->delete('quiz/{id}',['uses' => 'QuizController@remove']);
//  Question Routes
    $router->get('question[/{id}]',['uses' => 'QuestionController@fetch']);
    $router->post('question/{id}',['uses' => 'QuestionController@create']);
    $router->delete('question/{id}',['uses' => 'QuestionController@remove']);
//  Rounds Routes
    $router->get('rounds/{id}[/{label}]',['uses' => 'QuizController@fetchRounds']);
    $router->post('rounds/{id}',['uses' => 'QuizController@createRound']);
    $router->put('rounds/{id}/{label}',['uses' => 'QuizController@updateRounds']);
//  11111 Routes
    $router->get('11111/director[/{id}]', ['uses' => 'OneController@fetchDirector']);
    $router->get('11111/rating[/{id}]', ['uses' => 'OneController@fetchRating']);
    $router->get('11111/sections/{type}[/{subtype}]', ['uses' => 'OneController@fetchSection']);
    $router->get('11111[/{id}]', ['uses' => 'OneController@fetch']);

    $router->post('11111/rating', ['uses' => 'OneController@createRating']);
    $router->post('11111', ['uses' => 'OneController@create']);
    
    $router->put('11111/rating[/{id}]', ['uses' => 'OneController@updateRating']);
    $router->put('11111/{id}', ['uses' => 'OneController@update']);
//  Dictionary Routers
    $router->get('dictionary/{action}/{word}', ['uses' => 'DictionaryController@find']);
    $router->post('dictionary/{type}', ['uses' => 'DictionaryController@create']);
//  Tracker Routers
    $router->get('tracker/{id}','TrackerController@fetch');
    $router->post('tracker/{type}/{id}','TrackerController@create');
//  Options Routers
    $router->get('options/{type}[/{value}]','OptionsController@fetch');
});
