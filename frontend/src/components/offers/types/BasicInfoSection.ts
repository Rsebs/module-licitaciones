type MoneyTypes = 'COP' | 'USD' | 'EUR'

export interface BasicInfoForm {
  objeto: string
  descripcion: string
  moneda: MoneyTypes | ''
  presupuesto: number | ''
  actividad_id: number | null
}
