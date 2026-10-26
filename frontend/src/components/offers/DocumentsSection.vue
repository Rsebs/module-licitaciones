<template>
  <section class="card mb-3" aria-labelledby="sec-documentos">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h2 class="h6 mb-0" id="sec-documentos">Documentos</h2>
      <button type="button" class="btn btn-sm btn-outline-primary" @click="openModal = true">
        Agregar documento
      </button>
    </div>
    <div class="card-body">
      <DocumentsTable
        v-model:documents="allDocuments"
        @delete-document="emits('delete-document', $event)"
      />
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
      />
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
        placeholder="Descripción corta del documento"
      ></textarea>

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
import ModalCard from '@/components/ui/ModalCard.vue'
import DocumentsTable from './DocumentsTable.vue'
import { ref, watch } from 'vue'
import type { AddNewsDocuments } from './types/DocumentsSection.ts'
import { notify } from '@/helpers/notify.ts'

interface Props {
  documents: AddNewsDocuments[]
}

const props = defineProps<Props>()

interface FormDocument {
  titulo: string
  descripcion: string
  archivo: File | null
}

const emits = defineEmits(['addDocument', 'delete-document'])

const formDocument = ref<FormDocument>({
  titulo: '',
  descripcion: '',
  archivo: null,
})

const maxMB = 10
const openModal = ref(false)
const keyDocumentField = ref(0)
const allDocuments = ref([...props.documents])

const getFile = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    formDocument.value.archivo = file
  }
}

const addDocument = () => {
  if (
    !formDocument.value.titulo.trim() ||
    !formDocument.value.descripcion.trim() ||
    !formDocument.value.archivo
  ) {
    notify(
      'Error',
      'Todos los campos son obligatorios. Por favor revise qué campos faltan por rellenar',
      'error',
    )
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

watch(
  () => props.documents,
  (newDocs) => {
    allDocuments.value = [...newDocs]
  },
)
</script>

<style scoped>
/* Neutraliza los estilos :valid/:invalid que Bootstrap aplica al agregar was-validated en el form padre */
.form-control:valid,
.form-control:invalid {
  border-color: var(--bs-border-color);
  background-image: none;
  box-shadow: none;
}

.form-control:focus:valid,
.form-control:focus:invalid {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
