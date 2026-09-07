<script setup>
import { defineEmits } from 'vue'

const emit = defineEmits(['close'])
</script>

<template>
  <div 
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    @click.self="emit('close')"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 w-[650px] max-h-[90vh] overflow-y-auto">
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
    </div>
  </div>
</template>
