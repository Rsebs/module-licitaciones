<template>
  <section class="card mb-3" aria-labelledby="sec-cronograma">
    <div class="card-header">
      <h2 class="h6 mb-0" id="sec-cronograma">Cronograma</h2>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Fecha de inicio -->
        <div class="col-sm-6 col-lg-3">
          <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
          <input
            v-model="modelValue.fecha_inicio"
            id="fecha_inicio"
            type="date"
            class="form-control"
            required
          />
          <div class="invalid-feedback">La fecha de inicio es obligatoria.</div>
        </div>

        <!-- Hora de inicio -->
        <div class="col-sm-6 col-lg-3">
          <label for="hora_inicio" class="form-label">Hora de inicio</label>
          <input
            v-model="modelValue.hora_inicio"
            id="hora_inicio"
            type="time"
            class="form-control"
            required
          />
          <div class="invalid-feedback">La hora de inicio es obligatoria.</div>
        </div>

        <!-- Fecha de cierre -->
        <div class="col-sm-6 col-lg-3">
          <label for="fecha_cierre" class="form-label">Fecha de cierre</label>
          <input
            v-model="modelValue.fecha_cierre"
            id="fecha_cierre"
            type="date"
            class="form-control"
            :min="modelValue.fecha_inicio || undefined"
            required
          />
          <div class="invalid-feedback">La fecha de cierre es obligatoria.</div>
        </div>

        <!-- Hora de cierre -->
        <div class="col-sm-6 col-lg-3">
          <label for="hora_cierre" class="form-label">Hora de cierre</label>
          <input
            v-model="modelValue.hora_cierre"
            id="hora_cierre"
            type="time"
            class="form-control"
            required
          />
          <div class="invalid-feedback">La hora de cierre es obligatoria.</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { ScheduleSectionForm } from './types/ScheduleSection'

const modelValue = defineModel<ScheduleSectionForm>({
  required: true,
})
</script>
