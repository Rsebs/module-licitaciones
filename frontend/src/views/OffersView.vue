<template>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Ofertas</h1>
    <div class="d-flex gap-2">
      <RouterLink :to="{ name: 'offer-form' }" class="btn btn-primary">Nueva oferta</RouterLink>
    </div>
  </div>

  <div class="card card-body mb-3">
    <div class="card mt-4">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col">Consecutivo</th>
              <th scope="col">Objeto</th>
              <th scope="col">Descripción</th>
              <th scope="col">Fecha de inicio</th>
              <th scope="col">Fecha de cierre</th>
              <th scope="col">Estado</th>
              <th scope="col" class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="7" class="text-center py-4">
                <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                Cargando ofertas…
              </td>
            </tr>
            <tr v-else-if="offers.length === 0">
              <td colspan="7" class="text-center py-4">No existen ofertas registradas.</td>
            </tr>
            <tr v-else v-for="o in offers" :key="o.id">
              <td class="text-nowrap fw-semibold num">{{ o.consecutivo }}</td>
              <td>
                <div class="cell-clip" :title="o.objeto">{{ o.objeto }}</div>
              </td>
              <td>
                <div class="cell-clip" :title="o.descripcion">{{ o.descripcion }}</div>
              </td>
              <td class="text-nowrap num">{{ o.fecha_inicio }}</td>
              <td class="text-nowrap num">{{ o.fecha_cierre }}</td>
              <td>
                <span class="badge">{{ o.estado }}</span>
              </td>
              <td class="text-end">
                <RouterLink
                  class="btn btn-sm btn-outline-primary"
                  :to="{ name: 'offer-details', params: { id: o.id } }"
                >
                  Ver detalle
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- <pre>{{ meta }}</pre> -->
  <div
    class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"
    v-if="offers.length > 0"
  >
    <small class="text-body-secondary"> Total de ofertas: {{ meta?.total }} </small>
    <nav aria-label="Paginación del listado" v-if="meta?.last_page && meta.last_page > 1">
      <ul class="pagination pagination-sm mb-0">
        <!-- Botón Anterior -->
        <li class="page-item" :class="{ disabled: (meta?.page ?? 1) <= 1 }">
          <button
            type="button"
            class="page-link"
            :disabled="(meta?.page ?? 1) <= 1"
            @click="onGetOffers({ page: (meta?.page ?? 1) - 1 })"
          >
            Anterior
          </button>
        </li>

        <!-- Números de página -->
        <li
          v-for="(p, i) in meta?.last_page"
          :key="i"
          class="page-item"
          :class="{ active: p === meta?.page }"
        >
          <button type="button" class="page-link" @click="onGetOffers({ page: p })">
            {{ p }}
          </button>
        </li>

        <!-- Botón Siguiente -->
        <li class="page-item" :class="{ disabled: (meta?.page ?? 1) >= (meta?.last_page ?? 1) }">
          <button
            type="button"
            class="page-link"
            :disabled="(meta?.page ?? 1) >= (meta?.last_page ?? 1)"
            @click="onGetOffers({ page: (meta?.page ?? 1) + 1 })"
          >
            Siguiente
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import AxiosService from '@/api/services/ApiService'
import type { ApiOffer } from '@/api/types/offer.type'
import type { APIPaginate } from '@/api/types/axiosResponse.type'

const offers = ref<ApiOffer[]>([])
const meta = ref<APIPaginate>()
const isLoading = ref(false)

const onGetOffers = async (queryParams: Record<string, unknown> = {}) => {
  try {
    isLoading.value = true
    const response = await AxiosService.get<ApiOffer[]>('/ofertas', queryParams)
    offers.value = response.data
    meta.value = response.meta
  } catch (error) {
    console.error('Hubo un error con la petición', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  onGetOffers()
})
</script>
