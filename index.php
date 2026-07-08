<?php
    require_once __DIR__ . '/config/config.php';
	require_once __DIR__ . '/app/helpers/functions.php';
	require_once __DIR__ . '/app/core/Database.php';
	require_once __DIR__ . '/app/core/Model.php';
	require_once __DIR__ . '/app/core/Controller.php';
	require_once __DIR__ . '/app/core/Router.php';
	require_once __DIR__ . '/app/models/User.php';

$router = new Router();
$router->dispatch();