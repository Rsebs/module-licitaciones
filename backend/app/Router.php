<?php

namespace App;

class Router
{
  private $rutas = [];

  public function get($patron, $controlador, $accion)
  {
    $this->agregar('GET', $patron, $controlador, $accion);
  }

  public function post($patron, $controlador, $accion)
  {
    $this->agregar('POST', $patron, $controlador, $accion);
  }

  private function agregar($metodo, $patron, $controlador, $accion)
  {
    // '/api/ofertas/{id}'  se convierte en  #^/api/ofertas/(?P<id>\d+)$#
    $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $patron) . '$#';
    $this->rutas[] = [$metodo, $regex, $controlador, $accion];
  }

  public function dispatch($metodo, $ruta)
  {
    $ruta = '/' . trim($ruta, '/');   // ignora la barra final: /api/ofertas/ = /api/ofertas

    foreach ($this->rutas as $r) {
      list($m, $regex, $controlador, $accion) = $r;

      if ($m === $metodo && preg_match($regex, $ruta, $coincidencias)) {
        // Los valores de {id}, {docId}... llegan al controlador como números
        $params = [];
        foreach ($coincidencias as $clave => $valor) {
          if (is_string($clave)) {
            $params[$clave] = (int) $valor;
          }
        }
        (new $controlador())->$accion($params);
        return;
      }
    }

    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => 'Ruta no encontrada']);
  }
}
