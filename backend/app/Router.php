<?php

namespace App;

class Router
{
  private $routes = [];

  public function get($pattern, $controller, $action)
  {
    $this->add('GET', $pattern, $controller, $action);
  }

  public function post($pattern, $controller, $action)
  {
    $this->add('POST', $pattern, $controller, $action);
  }

  private function add($method, $pattern, $controller, $action)
  {
    // '/api/ofertas/{id}'  becomes  #^/api/ofertas/(?P<id>\d+)$#
    $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $pattern) . '$#';
    $this->routes[] = [$method, $regex, $controller, $action];
  }

  public function dispatch($method, $path)
  {
    $path = '/' . trim($path, '/');   // ignores the trailing slash

    foreach ($this->routes as $route) {
      list($routeMethod, $regex, $controller, $action) = $route;

      if ($routeMethod === $method && preg_match($regex, $path, $matches)) {
        // Values of {id}, {docId}... reach the controller as integers
        $params = [];
        foreach ($matches as $key => $value) {
          if (is_string($key)) {
            $params[$key] = (int) $value;
          }
        }
        (new $controller())->$action($params);
        return;
      }
    }

    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => 'Ruta no encontrada']);
  }
}
