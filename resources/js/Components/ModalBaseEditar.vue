<script setup>
import { defineEmits } from 'vue'
import { EscClose } from '@/Utils/EscClose'

const emit = defineEmits(['close', 'save'])

EscClose(() => {
  emit('close')
})
</script>

<template>
  <div
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    @click.self="emit('close')"
  >
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-5xl h-5/6 flex flex-col p-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <slot name="header"></slot>
        <button @click="emit('close')" class="text-gray-500 hover:text-gray-700">
          <span class="material-icons">close</span>
        </button>
      </div>

      <!-- Contenido dinámico -->
      <div class="flex-1 overflow-y-auto">
        <slot></slot>
      </div>

      <!-- Footer fijo -->
      <div class="flex justify-end mt-6 space-x-4">
        <slot name="footer"></slot>
        <button
          @click="emit('close')"
          class="px-6 py-2 bg-gray-300 text-[#2D2B5B] rounded hover:bg-gray-400"
        >
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>