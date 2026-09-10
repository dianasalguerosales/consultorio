<script setup>
import ModalCapa from '@/Components/ModalCapa.vue'

const emit = defineEmits(['close', 'save'])
</script>

<template>
  <ModalCapa panel="max-w-[650px] max-h-[90vh] overflow-y-auto p-6" @close="emit('close')">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
      <slot name="header"></slot>
      <button @click="emit('close')" class="text-gray-500 hover:text-gray-700">
        <span class="material-icons">close</span>
      </button>
    </div>

    <!-- Contenido dinámico -->
    <slot></slot>

    <!-- Footer -->
    <div class="flex justify-end mt-6">
      <button @click="emit('close')"
        class="px-4 py-2 bg-gray-300 text-[#2D2B5B] rounded hover:bg-gray-400">
        Cerrar
      </button>
    </div>
  </ModalCapa>
</template>
