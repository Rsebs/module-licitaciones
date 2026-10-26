import type { OfertaEstado } from '@/api/types/offer.type'

export const getClassBadgeStateOffer = (estado: OfertaEstado | string): string => {
  switch (estado) {
    case 'Activo':
      return 'bg-primary'
    case 'Publicada':
      return 'bg-success'
    case 'En evaluación':
      return 'bg-warning text-dark'
    case 'Finalizada':
      return 'bg-secondary'
    default:
      return 'bg-light text-dark border'
  }
}
