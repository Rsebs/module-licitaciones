<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
  protected $table = 'ofertas';

  const CREATED_AT = 'creado_en';
  const UPDATED_AT = 'actualizado_en';

  const CONSECUTIVE_PREFIX = 'PO';

  protected $fillable = [
    'objeto',
    'descripcion',
    'moneda',
    'presupuesto',
    'actividad_id',
    'fecha_inicio',
    'hora_inicio',
    'fecha_cierre',
    'hora_cierre',
  ];

  public function actividad()
  {
    return $this->belongsTo(Actividad::class, 'actividad_id');
  }

  public function documentos()
  {
    return $this->hasMany(OfertaDocumento::class, 'licitacion_id');
  }

  public static function nextConsecutive()
  {
    $year   = date('y');
    $prefix = self::CONSECUTIVE_PREFIX;

    $last = self::where('consecutivo', 'like', $prefix . '-%-' . $year)
      ->selectRaw("MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(consecutivo, '-', 2), '-', -1) AS UNSIGNED)) AS last_number")
      ->value('last_number');

    return sprintf('%s-%04d-%s', $prefix, ((int) $last) + 1, $year);
  }
}
