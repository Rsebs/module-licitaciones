<template>
  <section class="card mb-3" aria-labelledby="sec-documentos">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h2 class="h6 mb-0" id="sec-documentos">Documentos</h2>
      <button type="button" class="btn btn-sm btn-outline-primary">Agregar documento</button>
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
            <tr>
              <td>Documento de prueba</td>
              <td class="text-break">Documento de prueba. Descripción</td>
              <td><a href="#">prueba.pdf</a></td>
              <td class="text-nowrap">2026-10-23 10:00</td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger">Quitar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts"></script>
