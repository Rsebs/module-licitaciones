<?php

namespace App\Controllers;

use App\Models\Actividad;
use App\Models\Oferta;
use Illuminate\Database\Capsule\Manager as Capsule;

class OfertaController extends Controller
{
  public function index()
  {
    $page    = max(1, (int) $this->query('page', 1));
    $perPage = min(50, max(1, (int) $this->query('per_page', 10)));

    $query = Oferta::query();
    foreach (['consecutivo', 'objeto', 'descripcion'] as $field) {
      $value = $this->query($field);
      if ($value !== '') {
        $query->where($field, 'like', '%' . $value . '%');
      }
    }

    $total  = $query->count();
    $offers = $query->orderBy('id', 'desc')->forPage($page, $perPage)->get();

    $this->json([
      'data' => $offers,
      'meta' => [
        'page'      => $page,
        'per_page'  => $perPage,
        'total'     => $total,
        'last_page' => max(1, (int) ceil($total / $perPage)),
      ],
    ]);
  }

  public function show($params)
  {
    $offer = Oferta::with(['actividad', 'documentos'])->find($params['id']);

    if (!$offer) {
      $this->json(['message' => 'La oferta no existe.'], 404);
      return;
    }

    $this->json(['data' => $offer]);
  }

  public function store($params)
  {
    list($data, $errors) = $this->validateOffer($this->input());

    if ($errors) {
      $this->json(['message' => 'Revisa los campos marcados.', 'errors' => $errors], 422);
      return;
    }

    $connection = Capsule::connection();
    $row = $connection->selectOne("SELECT GET_LOCK('consecutivo_ofertas', 10) AS acquired");
    if (!$row || (int) $row->acquired !== 1) {
      $this->json(['message' => 'El sistema está ocupado. Inténtalo de nuevo.'], 503);
      return;
    }

    try {
      $offer = $connection->transaction(function () use ($data) {
        $new = new Oferta($data);
        $new->consecutivo = Oferta::nextConsecutive();
        $new->estado = 'Borrador';
        $new->save();
        return $new;
      });
    } finally {
      $connection->selectOne("SELECT RELEASE_LOCK('consecutivo_ofertas')");
    }

    $this->json([
      'message' => 'Oferta creada correctamente.',
      'data'    => Oferta::with(['actividad', 'documentos'])->find($offer->id),
    ], 201);
  }

  public function update($params)
  {
    $offer = Oferta::find($params['id']);

    if (!$offer) {
      $this->json(['message' => 'La oferta no existe.'], 404);
      return;
    }

    list($data, $errors) = $this->validateOffer($this->input());

    if ($errors) {
      $this->json(['message' => 'Revisa los campos marcados.', 'errors' => $errors], 422);
      return;
    }

    $offer->fill($data);
    $offer->save();

    $this->json([
      'message' => 'Oferta actualizada correctamente.',
      'data'    => Oferta::with(['actividad', 'documentos'])->find($offer->id),
    ]);
  }

  private function validateOffer(array $input)
  {
    $fields = [
      'objeto',
      'descripcion',
      'moneda',
      'presupuesto',
      'actividad_id',
      'fecha_inicio',
      'hora_inicio',
      'fecha_cierre',
      'hora_cierre'
    ];
    $data   = [];
    $errors = [];

    foreach ($fields as $field) {
      $data[$field] = (isset($input[$field]) && is_scalar($input[$field])) ? trim((string) $input[$field]) : '';
      if ($data[$field] === '') {
        $errors[$field][] = 'Este campo es obligatorio.';
      }
    }

    if ($data['objeto'] !== '' && mb_strlen($data['objeto']) > 150) {
      $errors['objeto'][] = 'No puede superar 150 caracteres.';
    }
    if ($data['descripcion'] !== '' && mb_strlen($data['descripcion']) > 400) {
      $errors['descripcion'][] = 'No puede superar 400 caracteres.';
    }
    if ($data['moneda'] !== '' && !in_array($data['moneda'], ['COP', 'USD', 'EUR'], true)) {
      $errors['moneda'][] = 'Debe ser COP, USD o EUR.';
    }
    if ($data['presupuesto'] !== '') {
      if (!preg_match('/^\d{1,13}(\.\d{1,2})?$/', $data['presupuesto'])) {
        $errors['presupuesto'][] = 'Solo números y punto decimal, máximo 2 decimales.';
      } elseif ((float) $data['presupuesto'] <= 0) {
        $errors['presupuesto'][] = 'Debe ser mayor a 0.';
      }
    }
    if ($data['actividad_id'] !== '') {
      if (!ctype_digit($data['actividad_id']) || !Actividad::where('id', (int) $data['actividad_id'])->exists()) {
        $errors['actividad_id'][] = 'La actividad no existe.';
      }
    }
    foreach (['fecha_inicio', 'fecha_cierre'] as $field) {
      if ($data[$field] !== '' && !$this->isValidDate($data[$field])) {
        $errors[$field][] = 'Fecha no válida (formato AAAA-MM-DD).';
      }
    }
    foreach (['hora_inicio', 'hora_cierre'] as $field) {
      if ($data[$field] !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $data[$field])) {
        $errors[$field][] = 'Hora no válida (formato HH:mm, 24 horas).';
      }
    }

    $scheduleFields = ['fecha_inicio', 'hora_inicio', 'fecha_cierre', 'hora_cierre'];
    if (!array_intersect_key($errors, array_flip($scheduleFields))) {
      if ($data['fecha_cierre'] < $data['fecha_inicio']) {
        $errors['fecha_cierre'][] = 'No puede ser menor a la fecha de inicio.';
      } elseif ($data['fecha_cierre'] === $data['fecha_inicio'] && $data['hora_cierre'] <= $data['hora_inicio']) {
        $errors['hora_cierre'][] = 'Si es el mismo día, debe ser mayor a la hora de inicio.';
      }
    }

    return [$data, $errors];
  }

  private function isValidDate($value)
  {
    $date = \DateTime::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
  }
}
