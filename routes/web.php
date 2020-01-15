<?php

$router->get('/', 'MainController@main');
$router->get('/{parent:different|semicolon|text|moment}/{title}', 'MainController@poem');
$router->get('project', 'MainController@project');
$router->get('author', 'MainController@author');
