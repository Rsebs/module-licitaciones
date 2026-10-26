<?php

namespace App\Controllers;

use App\Models\Actividad;

class ActividadController extends Controller
{
  public function index()
  {
    $query = $this->filteredQuery();
    $activities = $query->orderBy('codigo_producto', 'asc')->limit(50)->get();

    $this->json(['data' => $activities]);
  }

  private function filteredQuery()
  {
    $query = Actividad::query();

    foreach (['codigo_producto', 'producto'] as $field) {
      $value = $this->query($field);
      if ($value !== '') {
        $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
        $query->orWhere($field, 'like', '%' . $value . '%');
      }
    }

    return $query;
  }
}
