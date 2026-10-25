<template>
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
        <tr v-if="documentModelValue.length === 0">
          <td colspan="5" class="text-center">Sin documentos cargados</td>
        </tr>
        <tr v-else v-for="(document, index) in documentModelValue" :key="document.id">
          <td>{{ document.titulo }}</td>
          <td class="text-break">{{ document.descripcion }}</td>
          <td>
            <a :href="document.archivo" target="_blank">
              {{ document.nombre_original }}
            </a>
          </td>
          <td class="text-nowrap">{{ document.creado_en }}</td>
          <td class="text-end">
            <button
              type="button"
              class="btn btn-sm btn-outline-danger"
              @click="deleteDocument(index)"
            >
              Quitar
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { AddNewsDocuments } from './types/DocumentsSection'
import AxiosService from '@/api/services/ApiService'

const documentModelValue = defineModel<AddNewsDocuments[]>('documents', {
  required: true,
})
const isLoading = ref(false)

const deleteDocument = async (documentIndex: number) => {
  const document = documentModelValue.value[documentIndex]
  if (document?.isNew) {
    documentModelValue.value.splice(documentIndex, 1)
  } else {
    try {
      isLoading.value = true
      await AxiosService.delete(
        `/ofertas/${document?.licitacion_id}/documentos/${document?.id}/eliminar`,
      )
      documentModelValue.value.splice(documentIndex, 1)
    } catch (error) {
      console.error('Hubo un error con la petición', error)
    } finally {
      isLoading.value = false
    }
  }
}
</script>
