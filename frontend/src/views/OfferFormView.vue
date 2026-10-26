<template>
  <div class="container my-4">
    <div v-if="isLoading" class="text-center py-5">
      <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
      Cargando oferta…
    </div>

    <form v-else ref="formHtml" class="needs-validation" novalidate @submit.prevent="saveForm">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0">
          {{ isEdit ? 'Editar oferta' : 'Nueva oferta' }}
          <span v-if="isEdit" class="badge text-bg-light border ms-2">{{ offerConsecutive }}</span>
        </h1>
      </div>

      <BasicInfoSection v-model="form" />
      <ScheduleSection v-model="form" />
      <DocumentsSection
        v-if="isEdit"
        :documents="[...offerDocuments.map((d) => ({ ...d, isNew: false })), ...newOfferDocuments]"
        @addDocument="
          (document: ApiOfferDocument) => {
            newOfferDocuments.push({ ...document, isNew: true })
          }
        "
        @delete-document="
          (documentIndex) => {
            newOfferDocuments.splice(documentIndex, 1)
          }
        "
      />

      <div class="d-flex flex-wrap justify-content-end align-items-center gap-3 mt-4">
        <router-link class="btn btn-outline-secondary" to="/">Cancelar</router-link>
        <button type="submit" class="btn btn-primary" :disabled="isSaving">
          <span
            class="spinner-border spinner-border-sm me-1"
            aria-hidden="true"
            v-if="isSaving"
          ></span>
          {{ isSaving ? 'Guardando…' : isEdit ? 'Guardar cambios' : 'Guardar oferta' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import BasicInfoSection from '@/components/offers/BasicInfoSection.vue'
import ScheduleSection from '@/components/offers/ScheduleSection.vue'
import DocumentsSection from '@/components/offers/DocumentsSection.vue'

import { formatTime } from '@/helpers/formatters'
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AxiosService from '@/api/services/ApiService'

import type { ApiGetOfferResponse, ApiOfferDocument } from '@/api/types/offer.type'
import type { CreateForm } from '@/views/interfaces/OfferFormView.ts'
import type { AddNewsDocuments } from '@/components/offers/types/DocumentsSection'
import { notify } from '@/helpers/notify'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const isLoading = ref(false)
const isSaving = ref(false)

const form = ref<CreateForm>({
  objeto: '',
  descripcion: '',
  moneda: '',
  presupuesto: '',
  actividad_id: '',
  fecha_inicio: '',
  hora_inicio: '',
  fecha_cierre: '',
  hora_cierre: '',
})
const offerConsecutive = ref()

const offerDocuments = ref<ApiOfferDocument[]>([])
const newOfferDocuments = ref<AddNewsDocuments[]>([])

onMounted(async () => {
  if (!isEdit.value) return

  try {
    isLoading.value = true
    const response = await AxiosService.get<ApiGetOfferResponse>(`/ofertas/${route.params.id}`)

    offerConsecutive.value = response.data.consecutivo
    form.value = {
      objeto: response.data.objeto,
      descripcion: response.data.descripcion,
      moneda: response.data.moneda,
      presupuesto: response.data.presupuesto,
      actividad_id: response.data.actividad_id,
      hora_inicio: formatTime(response.data.hora_inicio),
      fecha_cierre: response.data.fecha_cierre,
      hora_cierre: formatTime(response.data.hora_cierre),
      fecha_inicio: response.data.fecha_inicio,
    }
    offerDocuments.value = response.data.documentos
  } catch (error) {
    console.error('Error al cargar la oferta', error)
  } finally {
    isLoading.value = false
  }
})

const validateForm = () => {
  const isFormFilled =
    form.value.objeto.trim().length > 0 &&
    form.value.descripcion.trim().length > 0 &&
    form.value.moneda !== '' &&
    form.value.presupuesto !== '' &&
    form.value.actividad_id !== null &&
    form.value.fecha_inicio !== '' &&
    form.value.hora_inicio !== '' &&
    form.value.fecha_cierre !== '' &&
    form.value.hora_cierre !== ''

  if (isEdit.value) {
    const allDocuments = [
      ...offerDocuments.value.map((d) => ({ ...d, isNew: false })),
      ...newOfferDocuments.value,
    ]

    if (allDocuments.length === 0) {
      notify('¡Error!', 'Debe agregar al menos un documento', 'error')

      return false
    }
    return isFormFilled && allDocuments.length > 0
  }

  return isFormFilled
}

const saveForm = async (event: Event) => {
  const target = event.target as HTMLFormElement
  if (!validateForm()) {
    target.classList.add('was-validated')
    return
  }

  try {
    isSaving.value = true

    if (isEdit.value) {
      await AxiosService.postWithFile(`/ofertas/${route.params.id}`, {
        ...form.value,
        documentos: newOfferDocuments.value.map((d) => ({
          title: d.titulo,
          description: d.descripcion,
        })),
        doc_archivo: newOfferDocuments.value.map((d) => d.archivo),
      })
    } else {
      await AxiosService.post('/ofertas', form.value)
    }

    router.push({ name: 'offers' })
  } catch (error) {
    console.error('Hubo un error con la petición', error)
  } finally {
    isSaving.value = false
  }
}
</script>
