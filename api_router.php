<?php

require_once 'app/controller/editor-api.controller.php';
require_once 'app/controller/game-api.controller.php';
require_once 'libs/router/router.php';

$router = new Router();

$router->addRoute('editors',       'GET',      'EditorController', 'getEditors');
$router->addRoute('editors/:id',   'GET',      'EditorController', 'getEditor');
$router->addRoute('editors',       'POST',     'EditorController', 'createEditor');
$router->addRoute('editors/:id',   'DELETE',   'EditorController', 'deleteEditor');
$router->addRoute('editors/:id',   'PUT',      'EditorController', 'updateEditor');



$router->addRoute('games',         'GET',      'GameController', 'getGames');
$router->addRoute('games/:id',     'GET',      'GameController', 'getGame');
$router->addRoute('games',         'POST',     'GameController', 'createGame');
$router->addRoute('games/:id',     'DELETE',   'GameController', 'deleteGame');
$router->addRoute('games/:id',     'PUT',      'GameController', 'updateGame');

$router->route($_GET['resource'],$_SERVER['REQUEST_METHOD']);