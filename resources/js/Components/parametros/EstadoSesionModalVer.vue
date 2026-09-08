<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  estadoSesion: Object
})
const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-xl font-bold text-[#2D2B5B]">Detalle del Estado de Sesión</h2>
    </template>

    <div class="space-y-4">
      <p><strong>Nombre:</strong> {{ estadoSesion.nombre }}</p>
      <p><strong>Activo:</strong> {{ estadoSesion.activo ? 'Sí' : 'No' }}</p>
    </div>
  </ModalBaseVer>
</template>
