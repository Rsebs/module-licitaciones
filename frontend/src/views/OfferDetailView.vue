<template>
  <div v-if="isLoading" class="text-center py-5">
    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
    Cargando oferta…
  </div>

  <div v-if="hasError" class="text-center py-5">
    <p class="lead">Hubo un error al cargar la oferta</p>
    <RouterLink :to="{ name: 'offers' }" class="btn btn-primary"> Volver al listado </RouterLink>
  </div>
  <template v-else>
    <!-- <div v-if="aviso" class="alert alert-success" role="status">{{ aviso }}</div> -->

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
      <div>
        <p class="mb-1 text-body-secondary num">{{ offer?.consecutivo }} - {{ offer?.estado }}</p>
        <h1 class="h3 mb-0">{{ offer?.objeto }}</h1>
      </div>
      <div class="d-flex gap-2">
        <RouterLink :to="{ name: 'offers' }" class="btn btn-outline-secondary">
          Volver al listado
        </RouterLink>
        <RouterLink :to="{ name: 'offer-form', params: { id: offer?.id } }" class="btn btn-primary">
          Editar oferta
        </RouterLink>
      </div>
    </div>

    <NavTabs :tabs="tabs">
      <template #info>
        <dl class="row mb-0">
          <dt class="col-sm-3">Objeto</dt>
          <dd class="col-sm-9">{{ offer?.objeto }}</dd>
          <dt class="col-sm-3">Descripción</dt>
          <dd class="col-sm-9 text-break">{{ offer?.descripcion }}</dd>
          <dt class="col-sm-3">Moneda</dt>
          <dd class="col-sm-9">{{ offer?.moneda }}</dd>
          <dt class="col-sm-3">Presupuesto</dt>
          <dd class="col-sm-9 num">{{ offer?.presupuesto }}</dd>
          <dt class="col-sm-3">Actividad</dt>
          <dd class="col-sm-9">
            <template v-if="offer?.actividad">
              <span class="num">{{ offer?.actividad.codigo_producto }}</span> ·
              {{ offer?.actividad.producto }}
              <div class="small text-body-secondary">
                {{ offer?.actividad.segmento }} › {{ offer?.actividad.familia }} ›
                {{ offer?.actividad.clase }}
              </div>
            </template>
          </dd>
        </dl>
      </template>

      <template #schedule>
        <dl class="row mb-0">
          <dt class="col-sm-3">Fecha de inicio</dt>
          <dd class="col-sm-9 num">{{ offer?.fecha_inicio }}</dd>
          <dt class="col-sm-3">Hora de inicio</dt>
          <dd class="col-sm-9 num">{{ offer?.hora_inicio }}</dd>
          <dt class="col-sm-3">Fecha de cierre</dt>
          <dd class="col-sm-9 num">{{ offer?.fecha_cierre }}</dd>
          <dt class="col-sm-3">Hora de cierre</dt>
          <dd class="col-sm-9 num">{{ offer?.hora_cierre }}</dd>
        </dl>
      </template>

      <template #documents>
        <div>
          <p v-if="offer?.documentos.length === 0" class="mb-0">
            Esta oferta aún no tiene documentos.
            <RouterLink :to="{ name: 'offer-form', params: { id: offer?.id } }">
              Agrégalos desde la edición.
            </RouterLink>
          </p>
          <DocumentsTable v-else v-model:documents="allDocuments" @reload-documents="onGetOffer" />
        </div>
      </template>
    </NavTabs>
  </template>
</template>

<script setup lang="ts">
import AxiosService from '@/api/services/ApiService'
import type { ApiGetOfferResponse } from '@/api/types/offer.type'
import DocumentsTable from '@/components/offers/DocumentsTable.vue'
import type { AddNewsDocuments } from '@/components/offers/types/DocumentsSection'
import NavTabs from '@/components/ui/NavTabs.vue'
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const offer = ref<ApiGetOfferResponse>()
const isLoading = ref(false)
const hasError = ref(false)

const tabs = [
  { id: 'info', titulo: 'Información básica' },
  { id: 'schedule', titulo: 'Cronograma' },
  { id: 'documents', titulo: 'Documentos (0)' },
]

const allDocuments = ref<AddNewsDocuments[]>([])

const onGetOffer = async () => {
  try {
    isLoading.value = true
    const response = await AxiosService.get<ApiGetOfferResponse>(`/ofertas/${route.params.id}`)
    offer.value = response.data
    if (offer.value && offer.value?.documentos && tabs[2]) {
      tabs[2].titulo = `Documentos (${offer.value.documentos.length})`
    }
    allDocuments.value = offer.value.documentos.map((d) => ({ ...d, isNew: false }))
  } catch (error) {
    console.error('Hubo un error con la petición', error)
    hasError.value = true
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  onGetOffer()
})
</script>

<style scoped></style>
