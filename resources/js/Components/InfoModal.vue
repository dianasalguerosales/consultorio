<script setup>
import ModalCapa from '@/Components/ModalCapa.vue'

defineProps({
  visible: Boolean,
  title: String,
  data: Object,
  avatar: String
})

const emit = defineEmits(['close'])

function formatKey(key) {
  return key
    .replace(/_/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase())
}
</script>

<template>
  <!-- Este modal vive siempre montado y se muestra con `visible`, así que la
       capa necesita saberlo para no cerrarse con Escape estando oculto. -->
  <ModalCapa :mostrar="visible" panel="max-w-sm p-6" @close="emit('close')">
    <!-- Encabezado con botón de cierre alineado a la derecha -->
    <div class="flex items-center justify-between border-b border-caine-celeste pb-3 mb-4">
      <h2 class="text-lg font-bold">{{ title }}</h2>

      <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
        ✕
      </button>
    </div>

    <!-- Render dinámico de campos -->
    <div v-for="(value, key) in data" :key="key" class="mb-2">
      <p><strong>{{ formatKey(key) }}:</strong> {{ value }}</p>
    </div>
  </ModalCapa>
</template>
