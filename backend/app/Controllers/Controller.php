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

  protected function files($field)
  {
    if (!isset($_FILES[$field])) {
      return [];
    }

    $raw = $_FILES[$field];
    if (!is_array($raw['name'])) {
      return [$raw];
    }

    $list = [];
    foreach ($raw['name'] as $i => $name) {
      $list[] = [
        'name' => $name,
        'tmp_name' => $raw['tmp_name'][$i],
        'error' => $raw['error'][$i],
        'size' => $raw['size'][$i],
      ];
    }
    return $list;
  }

  protected function bodyExceedsLimit()
  {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    return stripos($contentType, 'multipart/form-data') === 0
      && empty($_POST)
      && empty($_FILES)
      && !empty($_SERVER['CONTENT_LENGTH']);
  }
}
