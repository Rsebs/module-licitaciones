<?php

namespace App\Controllers;

use App\FileUploader;
use App\Models\Actividad;
use App\Models\Oferta;
use App\Models\OfertaDocumento;
use App\XlsxWriter;
use Illuminate\Database\Capsule\Manager as Capsule;

class OfertaController extends Controller
{
  public function index()
  {
    $page = max(1, (int) $this->query('page', 1));
    $perPage = min(50, max(1, (int) $this->query('per_page', 10)));

    $query = $this->filteredQuery();

    $total  = $query->count();
    $offers = $query->orderBy('id', 'desc')->forPage($page, $perPage)->get();

    foreach ($offers as $offer) {
      if ($offer->estado !== 'Finalizada')
        $offer->estado = $this->computeEstatus($offer);
    }

    $this->json([
      'data' => $offers,
      'meta' => [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
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

    if ($offer->estado !== 'Finalizada')
      $offer->estado = $this->computeEstatus($offer);

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
        $new->estado = 'Activo';
        $new->save();
        return $new;
      });
    } finally {
      $connection->selectOne("SELECT RELEASE_LOCK('consecutivo_ofertas')");
    }

    $this->json([
      'message' => 'Oferta creada correctamente.',
      'data' => Oferta::with(['actividad', 'documentos'])->find($offer->id),
    ], 201);
  }

  public function update($params)
  {
    if ($this->bodyExceedsLimit()) {
      $this->json(['message' => 'Los archivos superan el tamaño total que permite el servidor (post_max_size).'], 413);
      return;
    }

    $offer = Oferta::find($params['id']);

    if (!$offer) {
      $this->json(['message' => 'La oferta no existe.'], 404);
      return;
    }

    if ($offer->estado !== 'Activo') {
      $this->json(['message' => 'Solo se puede editar una oferta en estado Activo.'], 422);
      return;
    }

    $input = $this->input();
    list($data, $errors) = $this->validateOffer($input);

    $documents = [];
    if (isset($input['documentos']))
      $documents = $this->readNewDocuments($input['documentos'], $errors);

    if ($offer->documentos()->count() + count($documents) < 1) {
      $errors['documentos'][] = 'Debes registrar al menos un documento (PDF o ZIP).';
    }

    if ($errors) {
      $this->json(['message' => 'Revisa los campos marcados.', 'errors' => $errors], 422);
      return;
    }

    $savedFiles = [];
    try {
      Capsule::connection()->transaction(function () use ($offer, $data, $documents, &$savedFiles) {
        $offer->fill($data);
        $offer->save();

        foreach ($documents as $document) {
          $path = FileUploader::save($document['file']);
          $savedFiles[] = $path;

          $offer->documentos()->create([
            'titulo' => $document['title'],
            'descripcion' => $document['description'],
            'archivo' => $path,
            'nombre_original' => $document['original_name'],
          ]);
        }
      });
    } catch (\Throwable $e) {
      foreach ($savedFiles as $path) {
        FileUploader::delete($path);
      }
      throw $e;
    }

    $this->json([
      'message' => 'Oferta actualizada correctamente.',
      'data' => Oferta::with(['actividad', 'documentos'])->find($offer->id),
    ]);
  }

  public function finalize($params)
  {
    $offer = Oferta::find($params['id']);

    if (!$offer) {
      $this->json(['message' => 'La oferta no existe.'], 404);
      return;
    }

    $actualStatus = $this->computeEstatus($offer);

    if ($actualStatus !== 'En evaluación') {
      $this->json(['message' => 'Solo se puede finalizar una oferta que esté En evaluación.'], 422);
      return;
    }

    $offer->estado = 'Finalizada';
    $offer->save();

    $this->json([
      'message' => 'Oferta finalizada correctamente.',
      'data' => Oferta::with(['actividad', 'documentos'])->find($offer->id),
    ]);
  }

  public function download($params)
  {
    $document = OfertaDocumento::where('id', $params['docId'])
      ->where('licitacion_id', $params['id'])
      ->first();

    $path = $document ? FileUploader::absolutePath($document->archivo) : null;

    if ($path === null) {
      $this->json(['message' => 'El documento no existe o ya no está disponible.'], 404);
      return;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $fileName = trim((string) preg_replace('/[^\w\- ]+/u', '', $document->titulo));
    $fileName = ($fileName !== '' ? $fileName : 'documento') . '.' . $extension;

    header('Content-Type: ' . FileUploader::mimeType($extension));
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="document.' . $extension . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
  }

  public function deleteFile($params)
  {
    $document = OfertaDocumento::find($params['docId']);
    if (!$document) {
      $this->json(['message' => 'El documento no existe o ya no está disponible.'], 404);
      return;
    }

    FileUploader::delete($document->archivo);
    $document->delete();

    $this->json([
      'message' => 'Documento eliminado correctamente.',
    ]);
  }

  private function readNewDocuments(array $inputDocuments, array &$errors)
  {
    $files = $this->files('doc_archivo');
    $count = count($inputDocuments);
    $documents = [];
    for ($i = 0; $i < $count; $i++) {
      $title = isset($inputDocuments[$i]['title']) ? $inputDocuments[$i]['title'] : '';
      $description = isset($inputDocuments[$i]['description']) ? $inputDocuments[$i]['description'] : '';
      $file = isset($files[$i]) ? $files[$i] : null;

      $problems = [];
      if ($title === '') {
        $problems[] = 'el título es obligatorio';
      } elseif (mb_strlen($title) > 100) {
        $problems[] = 'el título no puede superar 100 caracteres';
      }
      if ($description === '') {
        $problems[] = 'la descripción es obligatoria';
      } elseif (mb_strlen($description) > 200) {
        $problems[] = 'la descripción no puede superar 200 caracteres';
      }
      $fileError = ($file === null) ? 'Debes seleccionar un archivo.' : FileUploader::validate($file);
      if ($fileError !== null) {
        $problems[] = rtrim(lcfirst($fileError), '.');
      }

      if ($problems) {
        $errors['documentos'][] = 'Documento ' . ($i + 1) . ': ' . implode('; ', $problems) . '.';
        continue;
      }

      $documents[] = ['title' => $title, 'description' => $description, 'file' => $file, 'original_name' => $file['name']];
    }

    return $documents;
  }

  public function export($params)
  {
    $excel = new XlsxWriter(
      'Ofertas',
      ['Consecutivo', 'Objeto', 'Descripción', 'Fecha de inicio', 'Fecha de cierre', 'Estado'],
      [16, 40, 60, 16, 16, 14]
    );

    foreach ($this->filteredQuery()->orderBy('id', 'desc')->cursor() as $offer) {
      $excel->addRow([
        $offer->consecutivo,
        $offer->objeto,
        $offer->descripcion,
        $offer->fecha_inicio,
        $offer->fecha_cierre,
        $offer->estado,
      ]);
    }

    $path = $excel->save();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="ofertas_' . date('Ymd_His') . '.xlsx"');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: no-store');
    readfile($path);
    unlink($path);
  }

  private function filteredQuery()
  {
    $query = Oferta::query();

    foreach (['consecutivo', 'objeto', 'descripcion'] as $field) {
      $value = $this->query($field);
      if ($value !== '') {
        $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
        $query->where($field, 'like', '%' . $value . '%');
      }
    }

    return $query;
  }

  /**
   * Calcula el estado de la oferta basándose en las fechas actuales.
   * - Activo: creada, aún no ha llegado la fecha de inicio.
   * - Publicada: la fecha/hora de inicio ya pasó pero no la de cierre.
   * - En evaluación: la fecha/hora de cierre ya pasó.
   * - Finalizada: el evaluador la marcó como finalizada (estado persistente en BD).
   */
  private function computeEstatus(Oferta $offer): string
  {
    if ($offer->estado === 'Finalizada')
      return 'Finalizada';

    $now = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
    $fechaInicio = \DateTime::createFromFormat('Y-m-d H:i:s', $offer->fecha_inicio . ' ' . $offer->hora_inicio);
    $fechaCierre = \DateTime::createFromFormat('Y-m-d H:i:s', $offer->fecha_cierre . ' ' . $offer->hora_cierre);

    if ($fechaCierre && $now >= $fechaCierre)
      return 'En evaluación';

    if ($fechaInicio && $now >= $fechaInicio)
      return 'Publicada';

    return 'Activo';
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
    $data = [];
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
