<?php

use vendor\routing\Route;

$router = new Route();

$router->get('/', [\controllers\main\MainController::class, "index"]);
$router->get('/1', [\controllers\main\MainController::class, 'index']);

$router->resolve();