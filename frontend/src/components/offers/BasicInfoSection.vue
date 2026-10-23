<template>
  <section class="card mb-3" aria-labelledby="sec-basica">
    <div class="card-header">
      <h2 class="h6 mb-0" id="sec-basica">Información básica</h2>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Objeto -->
        <div class="col-12">
          <label for="objeto" class="form-label">Objeto</label>
          <input
            v-model="modelValue.objeto"
            id="objeto"
            type="text"
            class="form-control"
            maxlength="150"
            autocomplete="off"
            placeholder="¿Qué se compra o contrata?"
            required
          />
          <div class="invalid-feedback">El objeto es obligatorio.</div>

          <div class="d-flex justify-content-end mt-1">
            <span class="form-text mt-0">{{ modelValue.objeto.length }}/150</span>
          </div>
        </div>

        <!-- Descripción / Alcance -->
        <div class="col-12">
          <label for="descripcion" class="form-label">Descripción / Alcance</label>
          <textarea
            v-model="modelValue.descripcion"
            id="descripcion"
            class="form-control"
            rows="4"
            maxlength="400"
            placeholder="Detalles completos..."
            required
          ></textarea>
          <div class="invalid-feedback">La descripción es obligatoria.</div>

          <div class="d-flex justify-content-end mt-1">
            <span class="form-text mt-0">{{ modelValue.descripcion.length }}/400</span>
          </div>
        </div>

        <!-- Moneda -->
        <div class="col-md-4">
          <label for="moneda" class="form-label">Moneda</label>
          <select v-model="modelValue.moneda" id="moneda" class="form-select" required>
            <option value="" disabled selected>Selecciona una moneda</option>
            <option value="COP">COP</option>
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
          </select>
          <div class="invalid-feedback">La moneda es obligatoria.</div>
        </div>

        <!-- Presupuesto -->
        <div class="col-md-8">
          <label for="presupuesto" class="form-label">Presupuesto</label>
          <div class="input-group">
            <span class="input-group-text">{{ modelValue.moneda || '—' }}</span>
            <input
              v-model="modelValue.presupuesto"
              id="presupuesto"
              type="text"
              inputmode="decimal"
              class="form-control"
              placeholder="0.00"
              autocomplete="off"
              required
            />
          </div>
          <div class="form-text">Solo números y punto decimal, hasta dos decimales.</div>
          <div class="invalid-feedback">El presupuesto es obligatorio.</div>
        </div>

        <!-- Actividad -->
        <div class="col-12">
          <label for="actividad" class="form-label"
            >Actividad (clasificador de bienes y servicios)</label
          >
          <select v-model="modelValue.actividad_id" id="actividad" class="form-select" required>
            <option value="" disabled selected>Selecciona una actividad</option>
            <option value="1">10151600 — Computadores de escritorio</option>
          </select>
          <div class="invalid-feedback">La actividad es obligatoria.</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { BasicInfoForm } from './types/BasicInfoSection'

const modelValue = defineModel<BasicInfoForm>({
  required: true,
})
</script>
