<?php

namespace App\Controllers;

abstract class Controller
{
  protected function json($data, $status = 200)
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  protected function query($key, $default = '')
  {
    if (isset($_GET[$key]) && is_string($_GET[$key])) {
      return trim($_GET[$key]);
    }
    return $default;
  }

  protected function input()
  {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    if (stripos($contentType, 'application/json') === 0) {
      $json = json_decode(file_get_contents('php://input'), true);
      return is_array($json) ? $json : [];
    }
    return $_POST;
  }
}
