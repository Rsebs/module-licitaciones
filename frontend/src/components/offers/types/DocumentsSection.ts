import type { ApiOfferDocument } from '@/api/types/offer.type'

export interface AddNewsDocuments extends ApiOfferDocument {
  isNew: boolean
}
