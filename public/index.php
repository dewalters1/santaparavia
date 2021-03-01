<?php

/**
 * Front controller
 *
 * PHP version 5.4
 */

/**
 * Composer Autoloader
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Sessions
 */
session_start();

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'index']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('register/activate/{token:[\da-f]+}', ['controller' => 'Register', 'action' => 'activate']);
$router->add('{controller}/{action}');
//$router->add('', ['controller' => 'Home', 'action' => 'index']);
////$router->add('login', ['controller' => 'Login', 'action' => 'index']);
////$router->add('register', ['controller' => 'Register', 'action' => 'index']);
//$router->add('{controller}/{action}');
//$router->add('{controller}/{id:\d+}/{action}');
//$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);

$router->dispatch($_SERVER['QUERY_STRING']);
