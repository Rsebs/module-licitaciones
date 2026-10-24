<?php

namespace App\Controllers;

use App\Models\Actividad;

class ActividadController extends Controller
{
  public function index()
  {
    $activities = Actividad::orderBy('producto')->limit(50)->get();

    $this->json(['data' => $activities]);
  }
}
