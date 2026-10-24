<?php

namespace App;

/**
 * Validates and stores uploaded documents (PDF or ZIP).
 * Files are saved OUTSIDE /public with a random name, so nobody can open them by URL.
 */
class FileUploader
{
  const MAX_SIZE_MB = 10;

  private static $allowed = [
    'pdf' => ['application/pdf'],
    'zip' => ['application/zip', 'application/x-zip-compressed', 'application/x-zip', 'multipart/x-zip'],
  ];

  public static function validate($file)
  {
    $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
      return 'Debes seleccionar un archivo.';
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
      return 'El archivo supera el tamaño que permite el servidor (upload_max_filesize).';
    }
    if ($error !== UPLOAD_ERR_OK) {
      return 'No se pudo cargar el archivo. Inténtalo de nuevo.';
    }

    $tmpPath = $file['tmp_name'];
    if (!is_uploaded_file($tmpPath)) {
      return 'El archivo recibido no es válido.';
    }

    $size = filesize($tmpPath);
    if ($size === 0) {
      return 'El archivo está vacío.';
    }
    if ($size > self::MAX_SIZE_MB * 1024 * 1024) {
      return 'El archivo supera el máximo de ' . self::MAX_SIZE_MB . ' MB.';
    }

    $extension = self::extension($file['name']);
    if (!isset(self::$allowed[$extension])) {
      return 'Solo se permiten archivos PDF o ZIP.';
    }

    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
    $header = (string) file_get_contents($tmpPath, false, null, 0, 1024);

    if ($extension === 'pdf') {
      $contentIsValid = strpos($header, '%PDF-') !== false;
    } else {
      $start = substr($header, 0, 4);
      $contentIsValid = ($start === "PK\x03\x04" || $start === "PK\x05\x06");
    }

    if (!in_array($mime, self::$allowed[$extension], true) || !$contentIsValid) {
      return 'El contenido del archivo no corresponde a un ' . strtoupper($extension) . ' válido.';
    }

    return null;
  }

  public static function save($file)
  {
    $directory = self::directory();
    if (!is_dir($directory)) {
      mkdir($directory, 0775, true);
    }

    $name = bin2hex(random_bytes(16)) . '.' . self::extension($file['name']);

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
      throw new \RuntimeException('No se pudo guardar el archivo cargado.');
    }

    return 'uploads/' . $name;
  }

  public static function absolutePath($relativePath)
  {
    $base = realpath(self::directory());
    $path = realpath(dirname(__DIR__) . '/storage/' . $relativePath);

    if ($base === false || $path === false || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
      return null;
    }
    return $path;
  }

  public static function delete($relativePath)
  {
    $path = self::absolutePath($relativePath);
    if ($path !== null) {
      unlink($path);
    }
  }

  public static function mimeType($extension)
  {
    return $extension === 'zip' ? 'application/zip' : 'application/pdf';
  }

  private static function directory()
  {
    return dirname(__DIR__) . '/storage/uploads';
  }

  private static function extension($fileName)
  {
    return strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));
  }
}
