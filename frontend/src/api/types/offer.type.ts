export interface ApiOffer {
  id: number
  consecutivo: string
  objeto: string
  descripcion: string
  moneda: string
  presupuesto: string
  actividad_id: number
  fecha_inicio: string
  hora_inicio: string
  fecha_cierre: string
  hora_cierre: string
  estado: string
  creado_en: string
  actualizado_en: string
}

export interface ApiGetOfferResponse {
  id: number
  consecutivo: string
  objeto: string
  descripcion: string
  moneda: string
  presupuesto: string
  actividad_id: number
  fecha_inicio: string
  hora_inicio: string
  fecha_cierre: string
  hora_cierre: string
  estado: string
  creado_en: string
  actualizado_en: string
  actividad: {
    id: number
    codigo_producto: string
    producto: string
    segmento: string
    familia: string
    clase: string
  }
  documentos: Array<unknown>
}
