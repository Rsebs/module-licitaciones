<?php
error_reporting(E_ALL & ~E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Router;
use App\Controllers\ActividadController;
use App\Controllers\OfertaController;

Database::connect();

$router = new Router();

$router->get('/api/actividades', ActividadController::class, 'index');
$router->get('/api/ofertas',     OfertaController::class,    'index');
$router->get('/api/ofertas/{id}', OfertaController::class,   'show');

$router->dispatch(
  $_SERVER['REQUEST_METHOD'],
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
