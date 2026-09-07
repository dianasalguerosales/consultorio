<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  servicio: Object
})
const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-xl font-bold text-[#2D2B5B]">Detalle del Servicio</h2>
    </template>

    <div class="space-y-4">
      <p><strong>Nombre:</strong> {{ servicio.nombre }}</p>
      <p><strong>Descripción:</strong> {{ servicio.descripcion }}</p>
      <p><strong>Activo:</strong> {{ servicio.activo ? 'Sí' : 'No' }}</p>
    </div>
  </ModalBaseVer>
</template>