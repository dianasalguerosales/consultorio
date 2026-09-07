<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  escolaridad: Object
})
const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-xl font-bold text-[#2D2B5B]">Detalle de la Escolaridad</h2>
    </template>

    <div class="space-y-4">
      <p><strong>Nombre:</strong> {{ escolaridad.nombre }}</p>
      <p><strong>Activo:</strong> {{ escolaridad.activo ? 'Sí' : 'No' }}</p>
    </div>
  </ModalBaseVer>
</template>
