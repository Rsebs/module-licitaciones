<?php

namespace App\Controllers;

use App\Models\Actividad;

class ActividadController extends Controller
{
  public function index()
  {
    $actividades = Actividad::orderBy('producto')->limit(50)->get();

    $this->json(['data' => $actividades]);
  }
}
