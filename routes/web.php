<?php

use Laravel\Lumen\Routing\Router;

/** @var $router Router  */
$router->get('/', ['as' => 'main', 'uses' => 'MainController@main']);
$router->get('/{parent:different|semicolon|text|moment}/{title}', ['as' => 'poem', 'uses' => 'MainController@poem']);
$router->get('project', ['as' => 'project', 'uses' => 'MainController@project']);
$router->get('author', ['as' => 'author', 'uses' => 'MainController@author']);
