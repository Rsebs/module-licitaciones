<?php

namespace App\Controllers;

use App\Models\Oferta;

class OfertaController extends Controller
{
  public function index()
  {
    $pagina    = max(1, (int) $this->query('page', 1));
    $porPagina = min(50, max(1, (int) $this->query('per_page', 10)));

    $consulta = Oferta::query();
    foreach (['consecutivo', 'objeto', 'descripcion'] as $campo) {
      $valor = $this->query($campo);
      if ($valor !== '') {
        $consulta->where($campo, 'like', '%' . $valor . '%');
      }
    }

    $total   = $consulta->count();
    $ofertas = $consulta->orderBy('id', 'desc')->forPage($pagina, $porPagina)->get();

    $this->json([
      'data' => $ofertas,
      'meta' => [
        'page'      => $pagina,
        'per_page'  => $porPagina,
        'total'     => $total,
        'last_page' => max(1, (int) ceil($total / $porPagina)),
      ],
    ]);
  }

  public function show($params)
  {
    $oferta = Oferta::with(['actividad', 'documentos'])->find($params['id']);

    if (!$oferta) {
      $this->json(['message' => 'La oferta no existe.'], 404);
      return;
    }

    $this->json(['data' => $oferta]);
  }
}
