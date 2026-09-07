<script setup>
import { EscClose } from '@/Components/EscClose'

defineProps({
  visible: Boolean,
  title: String,
  data: Object,
  avatar: String
})

const emit = defineEmits(['close'])

EscClose(() => {
  emit('close')
})

function formatKey(key) {
  return key
    .replace(/_/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase())
}
</script>

<template>
  <div v-if="visible" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
      
   <!-- Encabezado con botón de cierre alineado a la derecha -->
      <div class="flex items-center justify-between border-b border-caine-celeste pb-3 mb-4">

        <!-- Título a la izquierda -->
        <h2 class="text-lg font-bold">
          {{ title }}
        </h2>

        <!-- X a la derecha -->
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
          ✕
        </button>

      </div>

      <!-- Render dinámico de campos -->
      <div v-for="(value, key) in data" :key="key" class="mb-2">
        <p><strong>{{ formatKey(key) }}:</strong> {{ value }}</p>
      </div>
    </div>
  </div>
</template>
