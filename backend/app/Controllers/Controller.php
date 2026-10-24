<?php

namespace App\Controllers;

abstract class Controller
{
  protected function json(array|object $data, int $status = 200): void
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  protected function query(string $key, string $default = '')
  {
    if (isset($_GET[$key]) && is_string($_GET[$key])) {
      return trim($_GET[$key]);
    }
    return $default;
  }
}
