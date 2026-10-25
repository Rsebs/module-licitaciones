<?php
error_reporting(E_ALL & ~E_DEPRECATED);
date_default_timezone_set('America/Bogota');

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
header("Content-Type: application/json; charset=UTF-8");

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Router;
use App\Controllers\ActividadController;
use App\Controllers\OfertaController;

Database::connect();

$router = new Router();

$router->get('/api/actividades', ActividadController::class, 'index');
$router->get('/api/ofertas', OfertaController::class, 'index');
$router->get('/api/ofertas/{id}', OfertaController::class, 'show');
$router->post('/api/ofertas', OfertaController::class, 'store');
$router->post('/api/ofertas/{id}', OfertaController::class, 'update');
$router->get('/api/ofertas/{id}/documentos/{docId}/descargar', OfertaController::class, 'download');
$router->delete('/api/ofertas/{id}/documentos/{docId}/eliminar', OfertaController::class, 'deleteFile');

$router->dispatch(
  $_SERVER['REQUEST_METHOD'],
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
