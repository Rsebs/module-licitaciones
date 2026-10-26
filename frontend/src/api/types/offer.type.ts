import type { MoneyTypes } from '@/components/offers/types/BasicInfoSection'

export type OfertaEstado = 'Activo' | 'Publicada' | 'En evaluación' | 'Finalizada'

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
  estado: OfertaEstado
  creado_en: string
  actualizado_en: string
}

export interface ApiGetOfferResponse {
  id: number
  consecutivo: string
  objeto: string
  descripcion: string
  moneda: MoneyTypes
  presupuesto: number
  actividad_id: number
  fecha_inicio: string
  hora_inicio: string
  fecha_cierre: string
  hora_cierre: string
  estado: OfertaEstado
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
  documentos: Array<ApiOfferDocument>
}

export interface ApiOfferDocument {
  id: number
  licitacion_id: number
  titulo: string
  descripcion: string
  archivo: string
  nombre_original: string
  creado_en: string
}
