<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

interface Tab {
  name: string
  label: string
}

const router = useRouter()
const route = useRoute();

const activeTab = ref(route.name)
const tabs: Tab[] = [
  {
    name: 'offers',
    label: 'Ofertas',
  },
  {
    name: 'new-offer',
    label: 'Nueva Oferta',
  },
]


const selectTab = (tabName: string) => {
  activeTab.value = tabName
  router.push({ name: tabName })
}
</script>

<template>
  <div class="min-vh-100 d-flex flex-column bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm px-4">
      <div class="container-fluid">
        <span class="navbar-brand mb-0 h1 fs-4">Licitaciones</span>
      </div>
    </nav>

    <div class="container-fluid flex-grow-1 d-flex flex-column p-4">
      <ul class="nav nav-tabs  mb-4">
        <li class="nav-item" v-for="tab in tabs" :key="tab.name">
          <button
            :to="{ name: tab.name }"
            class="nav-link"
            :class="{ active: activeTab === tab.name }"
            @click="selectTab(tab.name)"
          >
            {{ tab.label }}
          </button>
        </li>
      </ul>

      <RouterView />
    </div>
  </div>
</template>

<style scoped>
/* Estilos adicionales opcionales para pulir la interfaz */
.nav-tabs .nav-link {
  font-weight: 500;
}
</style>
