<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Models\Actividad;
use App\UnspscImporter;

if (PHP_SAPI !== 'cli') {
  exit("Este script solo se ejecuta desde la terminal.\n");
}

error_reporting(E_ALL & ~E_DEPRECATED);
Database::connect();

$path = isset($argv[1]) ? $argv[1] : dirname(__DIR__) . '/storage/import/actividades.xlsx';

if (!is_file($path)) {
  fwrite(STDERR, "No se encontró el archivo: $path\n");
  fwrite(STDERR, "Copia el Excel del clasificador en storage/import/actividades.xlsx o indica su ruta:\n");
  fwrite(STDERR, "  composer import:activities -- ruta/al/archivo.xlsx\n");
  exit(1);
}

echo "Importando desde: $path\n";

$save = function (array $rows) {
  Actividad::upsert($rows, ['codigo_producto'], [
    'codigo_segmento',
    'segmento',
    'codigo_familia',
    'familia',
    'codigo_clase',
    'clase',
    'producto',
  ]);
};

$startedAt = microtime(true);
$batch = [];
$total = 0;

try {
  foreach ((new UnspscImporter())->read($path) as $row) {
    $batch[] = $row;

    if (count($batch) === 1000) {
      $save($batch);
      $total += count($batch);
      $batch = [];
      echo "\rFilas importadas: $total";
    }
  }

  if ($batch) {
    $save($batch);
    $total += count($batch);
  }
} catch (\Throwable $e) {
  $message = $e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getMessage();
  fwrite(STDERR, "\nError: " . $message . "\n");
  exit(1);
}

if ($total === 0) {
  fwrite(STDERR, "No se encontraron filas válidas en el archivo.\n");
  exit(1);
}

printf("\rFilas importadas: %d (%.1f segundos)\n", $total, microtime(true) - $startedAt);
