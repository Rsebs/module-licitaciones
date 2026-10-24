<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfertaDocumento extends Model
{
  protected $table = 'ofertas_documentos';

  const CREATED_AT = 'creado_en';
  const UPDATED_AT = null;
}
