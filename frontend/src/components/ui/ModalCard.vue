<template>
  <!-- Modal -->
  <div
    ref="modalCard"
    class="modal fade"
    id="modalCard"
    tabindex="-1"
    aria-labelledby="modalCardLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="modalCardLabel">{{ title }}</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <slot></slot>
        </div>

        <div v-if="$slots.footer" class="modal-footer">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import * as bootstrap from 'bootstrap'

interface Props {
  persistent?: boolean
  title: string
}

const props = withDefaults(defineProps<Props>(), {
  persistent: false,
})

const modelValue = defineModel<boolean>({ required: true })

const modalCard = ref<HTMLDivElement | null>(null)
let modalInstance: bootstrap.Modal | null = null

onMounted(() => {
  if (modalCard.value) {
    modalInstance = new bootstrap.Modal(modalCard.value, {
      backdrop: props.persistent ? 'static' : true,
      keyboard: !props.persistent,
    })

    modalCard.value.addEventListener('hidden.bs.modal', () => {
      if (modelValue.value) {
        modelValue.value = false
      }
    })
  }
})

watch(modelValue, (isOpen) => {
  if (!modalInstance) return

  if (isOpen) {
    modalInstance.show()
  } else {
    modalInstance.hide()
  }
})
</script>
