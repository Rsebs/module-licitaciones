<template>
  <section class="card mb-3" aria-labelledby="sec-documentos">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h2 class="h6 mb-0" id="sec-documentos">Documentos</h2>
      <button type="button" class="btn btn-sm btn-outline-primary" @click="openModal = true">
        Agregar documento
      </button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th scope="col">Título</th>
              <th scope="col">Descripción</th>
              <th scope="col">Archivo</th>
              <th scope="col">Registro</th>
              <th scope="col" class="text-end">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="documents.length === 0">
              <td colspan="5" class="text-center">Sin documentos cargados</td>
            </tr>
            <tr v-else v-for="document in documents" :key="document.id">
              <td>{{ document.titulo }}</td>
              <td class="text-break">{{ document.descripcion }}</td>
              <td>
                <a :href="document.archivo" target="_blank">
                  {{ document.nombre_original }}
                </a>
              </td>
              <td class="text-nowrap">{{ document.creado_en }}</td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger">Quitar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <ModalCard v-model="openModal" persistent title="Agregar Documento">
    <div class="col-12">
      <label for="doc-titulo" class="form-label">Titulo</label>
      <input
        v-model="formDocument.titulo"
        id="doc-titulo"
        type="text"
        class="form-control"
        maxlength="100"
        autocomplete="off"
        placeholder="Titulo del documento"
        required
      />
      <div class="invalid-feedback">El título es obligatorio.</div>

      <div class="d-flex justify-content-end mt-1">
        <span class="form-text mt-0">{{ formDocument.titulo.length }}/100</span>
      </div>
    </div>
    <div class="col-12">
      <label for="doc-descripcion" class="form-label">Descripción corta</label>
      <textarea
        v-model="formDocument.descripcion"
        id="doc-descripcion"
        class="form-control"
        rows="2"
        maxlength="200"
        required
        placeholder="Descripción corta del documento"
      ></textarea>
      <div class="invalid-feedback">La descripción es obligatoria.</div>

      <div class="d-flex justify-content-end mt-1">
        <span class="form-text mt-0">{{ formDocument.descripcion.length }}/100</span>
      </div>
    </div>
    <div class="mb-1">
      <label for="doc-archivo" class="form-label">Archivo</label>
      <input
        id="doc-archivo"
        type="file"
        class="form-control"
        accept=".pdf,.zip,application/pdf,application/zip"
        @change="getFile"
        :key="keyDocumentField"
      />
      <div class="form-text">PDF o ZIP, máximo {{ maxMB }} MB.</div>
    </div>

    <template #footer>
      <button type="button" class="btn btn-outline-secondary" @click="closeModal">Cancelar</button>
      <button type="button" class="btn btn-primary" @click="addDocument">Agregar</button>
    </template>
  </ModalCard>
</template>

<script setup lang="ts">
import type { ApiOfferDocument } from '@/api/types/offer.type'

import ModalCard from '@/components/ui/ModalCard.vue'
import { ref } from 'vue'

interface Props {
  documents: ApiOfferDocument[]
}

defineProps<Props>()

interface FormDocument {
  titulo: string
  descripcion: string
  archivo: File | null
}

const emits = defineEmits(['addDocument'])

const formDocument = ref<FormDocument>({
  titulo: '',
  descripcion: '',
  archivo: null,
})

const maxMB = 10
const openModal = ref(false)
const keyDocumentField = ref(0)

const getFile = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    formDocument.value.archivo = file
  }
}

const addDocument = () => {
  if (
    !formDocument.value.titulo ||
    !formDocument.value.descripcion ||
    !formDocument.value.archivo
  ) {
    return
  }
  emits('addDocument', formDocument.value)
  closeModal()
}

const closeModal = () => {
  formDocument.value = {
    titulo: '',
    descripcion: '',
    archivo: null,
  }
  openModal.value = false
  keyDocumentField.value += 1
}
</script>
