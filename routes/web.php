<?php

function parent_alias($parent)
{
    $aliases = [
        'different' => 'Разные стихотворения',
        'semicolon' => 'Двоеточие',
        'moment' => 'Мгновение',
        'text' => 'Проза',
    ];

    return array_key_exists($parent, $aliases) ? $aliases[$parent] : 'Другое';
}

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

$router->get('/', 'MainController@main');
$router->get('/{parent:different|semicolon|text|moment}/{title}', 'MainController@poem');
$router->get('project', 'MainController@project');
$router->get('author', 'MainController@author');
