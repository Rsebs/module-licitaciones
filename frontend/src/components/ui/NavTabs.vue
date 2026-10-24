<template>
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item" role="presentation" v-for="t in tabs" :key="t.id">
      <button
        type="button"
        class="nav-link"
        :class="{ active: activeTab === t.id }"
        role="tab"
        :aria-selected="activeTab === t.id ? 'true' : 'false'"
        @click="activeTab = t.id"
      >
        {{ t.titulo }}
      </button>
    </li>
  </ul>

  <div class="card card-body border-top-0 rounded-top-0" role="tabpanel">
    <template v-for="tab in tabs" :key="tab.id">
      <slot v-if="tab.id === activeTab" :name="tab.id"></slot>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps({
  tabs: {
    type: Array<{ id: string; titulo: string }>,
    required: true,
  },
})

const activeTab = ref(props.tabs[0]?.id)
</script>
