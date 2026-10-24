<template>
  ESTE ES EL DETALLE DE LA OFERTA: {{ $route.params.id }}

  <RouterLink :to="{ name: 'offers' }"> Volver al listado </RouterLink>
  <RouterLink :to="{ name: 'offer-form', params: { id: $route.params.id } }">
    Editar oferta
  </RouterLink>
</template>

<script setup lang="ts"></script>

<style scoped></style>
