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
        <template v-else v-for="(document, index) in documentModelValue" :key="document.id">
          <tr :class="{ 'table-success': document.isNew }">
            <td>{{ document.titulo }}</td>
            <td class="text-break">{{ document.descripcion }}</td>
            <td>
              <p
                class="link"
                @click="
                  downloadDocument(document.licitacion_id, document.id, document.nombre_original)
                "
              >
                {{ document.nombre_original }}
              </p>
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
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import type { AddNewsDocuments } from './types/DocumentsSection'
import AxiosService from '@/api/services/ApiService'
import { notify, warningNotify } from '@/helpers/notify'

const documentModelValue = defineModel<AddNewsDocuments[]>('documents', {
  required: true,
})

const emits = defineEmits(['reload-documents', 'delete-document'])

const deleteDocument = async (documentIndex: number) => {
  if (documentModelValue.value.length === 1) {
    notify('Error', 'No se puede eliminar el último documento', 'error')
    return
  }

  const response = await warningNotify()
  if (!response) return

  const document = documentModelValue.value[documentIndex]
  if (document?.isNew) {
    documentModelValue.value.splice(documentIndex, 1)
    notify('Documento eliminado', 'El documento ha sido eliminado correctamente', 'success')
    emits('delete-document', documentIndex)
  } else {
    try {
      await AxiosService.delete(
        `/ofertas/${document?.licitacion_id}/documentos/${document?.id}/eliminar`,
      )
      documentModelValue.value.splice(documentIndex, 1)
      notify('Documento eliminado', 'El documento ha sido eliminado correctamente', 'success')
      emits('reload-documents')
    } catch (error) {
      console.error('Hubo un error con la petición', error)
    }
  }
}

const downloadDocument = (licitacionId: number, documentId: number, fileName: string) => {
  try {
    AxiosService.download(`/ofertas/${licitacionId}/documentos/${documentId}/descargar`, fileName)
  } catch (error) {
    console.error('Hubo un error con la petición', error)
  }
}
</script>

<style scoped>
.link {
  color: blue;
  cursor: pointer;
  text-decoration: underline;
}

.link:hover {
  color: darkblue;
}
</style>
