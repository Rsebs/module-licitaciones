<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
  protected $table = 'ofertas';

  const CREATED_AT = 'creado_en';
  const UPDATED_AT = 'actualizado_en';

  public function actividad()
  {
    return $this->belongsTo(Actividad::class, 'actividad_id');
  }

  public function documentos()
  {
    return $this->hasMany(OfertaDocumento::class, 'licitacion_id');
  }
}
